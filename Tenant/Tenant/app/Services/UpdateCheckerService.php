<?php

namespace App\Services;

use App\Models\AppVersion;
use App\Models\School;
use App\Models\TenantUpdate;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UpdateCheckerService
{
    public function normalize(?string $version): string
    {
        $value = trim((string) $version);

        if ($value === '') {
            return '0.0.0';
        }

        return ltrim(strtolower($value), 'v');
    }

    public function compare(?string $left, ?string $right): int
    {
        return version_compare($this->normalize($left), $this->normalize($right));
    }

    public function isUpdateAvailable(?string $current, ?string $latest): bool
    {
        if (trim((string) $latest) === '') {
            return false;
        }

        return $this->compare($current, $latest) < 0;
    }

    /**
     * @return array{version: string|null, notes: string|null, source: string}
     */
    public function getLatestVersionInfo(): array
    {
        if (config('updates.github.enabled', false)) {
            $githubLatest = $this->fetchGithubLatestVersion();

            if ($githubLatest !== null) {
                return [
                    'version' => $githubLatest['version'],
                    'notes' => $githubLatest['notes'],
                    'source' => 'github',
                ];
            }
        }

        try {
            $active = AppVersion::query()
                ->where('is_active', true)
                ->latest('created_at')
                ->first();
        } catch (QueryException) {
            return [
                'version' => null,
                'notes' => null,
                'source' => 'unavailable',
            ];
        }

        if ($active) {
            return [
                'version' => $active->version,
                'notes' => $active->notes,
                'source' => 'central_active',
            ];
        }

        $latest = AppVersion::query()->latest('created_at')->first();

        if ($latest) {
            return [
                'version' => $latest->version,
                'notes' => $latest->notes,
                'source' => 'central_latest',
            ];
        }

        $releaseTagVersion = $this->extractVersionFromReleaseUrl(
            (string) config('app.release.github_url', '')
        );

        if ($releaseTagVersion !== null) {
            return [
                'version' => $releaseTagVersion,
                'notes' => null,
                'source' => 'release_url',
            ];
        }

        $configuredVersion = trim((string) config('app.version', ''));

        if ($configuredVersion !== '') {
            return [
                'version' => $configuredVersion,
                'notes' => null,
                'source' => 'app_config',
            ];
        }

        return [
            'version' => null,
            'notes' => null,
            'source' => 'unavailable',
        ];
    }

    /**
     * @return array{tenant_update: TenantUpdate, latest_version: string|null, update_available: bool, source: string}
     */
    public function checkForUpdates(School $school): array
    {
        $centralTenantId = $this->resolveCentralSchoolId($school);

        if (! $centralTenantId) {
            throw new \RuntimeException('Unable to resolve tenant mapping in central database.');
        }

        $currentVersion = (string) ($school->version ?: config('app.version', 'v1.0.0'));
        $latestInfo = $this->getLatestVersionInfo();
        $latestVersion = $latestInfo['version'];

        $tenantUpdate = TenantUpdate::query()->firstOrNew(['tenant_id' => $centralTenantId]);

        $resetAcknowledge = $tenantUpdate->exists
            && (string) $tenantUpdate->latest_seen_version !== (string) $latestVersion;

        $tenantUpdate->fill([
            'current_version' => $currentVersion,
            'last_checked_at' => now(),
            'latest_seen_version' => $latestVersion,
            'acknowledged_at' => $resetAcknowledge ? null : $tenantUpdate->acknowledged_at,
        ]);

        $tenantUpdate->save();

        return [
            'tenant_update' => $tenantUpdate,
            'latest_version' => $latestVersion,
            'update_available' => $this->isUpdateAvailable($currentVersion, $latestVersion),
            'source' => $latestInfo['source'],
        ];
    }

    public function acknowledgeUpdate(School $school): void
    {
        $centralTenantId = $this->resolveCentralSchoolId($school);

        if (! $centralTenantId) {
            return;
        }

        $tenantUpdate = TenantUpdate::query()->firstOrNew(['tenant_id' => $centralTenantId]);
        $tenantUpdate->fill([
            'current_version' => (string) ($school->version ?: config('app.version', 'v1.0.0')),
            'last_checked_at' => $tenantUpdate->last_checked_at ?: now(),
            'acknowledged_at' => now(),
        ]);
        $tenantUpdate->save();
    }

    public function resolveCentralSchoolId(School $tenantSchool): ?int
    {
        $table = 'schools';

        if (! DB::connection('central')->getSchemaBuilder()->hasTable($table)) {
            return null;
        }

        return DB::connection('central')->table($table)
            ->where('tenant_database', $tenantSchool->tenant_database)
            ->orWhere('tenant_domain', $tenantSchool->tenant_domain)
            ->value('id');
    }

    /**
     * @return array{version: string, notes: string|null}|null
     */
    private function fetchGithubLatestVersion(): ?array
    {
        $endpoint = trim((string) config('updates.github.latest_release_endpoint', ''));

        if ($endpoint === '') {
            $endpoint = $this->buildLatestReleaseEndpointFromReleaseUrl(
                (string) config('updates.github.release_url', config('app.release.github_url', ''))
            );
        }

        if ($endpoint === '') {
            return null;
        }

        try {
            $response = $this->githubClient()->get($endpoint);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $payload = $response->json();
        $tag = trim((string) ($payload['tag_name'] ?? ''));

        if ($tag === '') {
            return null;
        }

        return [
            'version' => $tag,
            'notes' => (string) ($payload['body'] ?? ''),
        ];
    }

    private function githubClient(): PendingRequest
    {
        $client = Http::withHeaders([
            'User-Agent' => 'EduProfile-TenantUpdateChecker',
            'Accept' => 'application/vnd.github+json',
        ])->timeout(10);

        $verifySsl = (bool) config('updates.github.verify_ssl', true);
        $caBundle = trim((string) config('updates.github.ca_bundle', ''));

        if (! $verifySsl) {
            return $client->withOptions(['verify' => false]);
        }

        if ($caBundle !== '') {
            return $client->withOptions(['verify' => $caBundle]);
        }

        return $client;
    }

    private function buildLatestReleaseEndpointFromReleaseUrl(string $releaseUrl): string
    {
        $url = trim($releaseUrl);

        if ($url === '') {
            return '';
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        if (count($segments) < 2) {
            return '';
        }

        $owner = $segments[0];
        $repo = $segments[1];

        return sprintf('https://api.github.com/repos/%s/%s/releases/latest', $owner, $repo);
    }

    private function extractVersionFromReleaseUrl(string $releaseUrl): ?string
    {
        $url = trim($releaseUrl);

        if ($url === '') {
            return null;
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        $tagIndex = array_search('tag', $segments, true);

        if ($tagIndex === false || ! isset($segments[$tagIndex + 1])) {
            return null;
        }

        $version = trim((string) $segments[$tagIndex + 1]);

        return $version !== '' ? $version : null;
    }
}
