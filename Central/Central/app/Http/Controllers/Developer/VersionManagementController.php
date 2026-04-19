<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use App\Models\SupportRequest;
use App\Models\TenantUpdate;
use App\Services\VersionComparisonService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VersionManagementController extends Controller
{
    public function __construct(private readonly VersionComparisonService $versionComparisonService)
    {
    }

    public function index(Request $request): View
    {
        $activeVersion = AppVersion::query()->where('is_active', true)->latest('created_at')->first();

        $versions = AppVersion::query()
            ->latest('created_at')
            ->paginate(8, ['*'], 'versions_page')
            ->withQueryString();

        $tenantUpdates = TenantUpdate::query()
            ->with('tenant:id,name,tenant_domain,tenant_database')
            ->latest('last_checked_at')
            ->paginate(12, ['*'], 'tenant_updates_page')
            ->withQueryString();

        $latestVersion = $activeVersion?->version;

        $tenantUpdateRows = $tenantUpdates->getCollection()->map(function (TenantUpdate $tenantUpdate) use ($latestVersion) {
            $visibleLatest = $tenantUpdate->latest_seen_version ?: $latestVersion;

            return [
                'model' => $tenantUpdate,
                'visible_latest' => $visibleLatest,
                'update_available' => $this->versionComparisonService->isOutdated($tenantUpdate->current_version, $visibleLatest),
            ];
        });

        $statusFilter = (string) $request->input('request_status', '');

        $supportRequests = SupportRequest::query()
            ->with('tenant:id,name,tenant_domain')
            ->when($statusFilter !== '', fn ($query) => $query->where('status', $statusFilter))
            ->latest('created_at')
            ->paginate(12, ['*'], 'support_requests_page')
            ->withQueryString();

        return view('developer.version-management.index', [
            'activeVersion' => $activeVersion,
            'versions' => $versions,
            'tenantUpdates' => $tenantUpdates,
            'tenantUpdateRows' => $tenantUpdateRows,
            'supportRequests' => $supportRequests,
            'requestStatusFilter' => $statusFilter,
        ]);
    }

    public function storeVersion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'version' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $isActive = (bool) ($validated['is_active'] ?? false);

        if ($isActive) {
            AppVersion::query()->update(['is_active' => false]);
        }

        AppVersion::query()->create([
            'version' => $validated['version'],
            'notes' => $validated['notes'] ?? null,
            'is_active' => $isActive,
        ]);

        return redirect()->route('developer.version-management.index')
            ->with('success', 'Version entry created successfully.');
    }

    public function activateVersion(AppVersion $appVersion): RedirectResponse
    {
        AppVersion::query()->update(['is_active' => false]);
        $appVersion->update(['is_active' => true]);

        return redirect()->route('developer.version-management.index')
            ->with('success', 'Selected version is now active.');
    }

    public function syncGithubLatest(): RedirectResponse
    {
        if (! config('updates.github.enabled', false)) {
            return redirect()->route('developer.version-management.index')
                ->with('error', 'GitHub sync is disabled by configuration.');
        }

        $endpoint = trim((string) config('updates.github.latest_release_endpoint', ''));

        if ($endpoint === '') {
            $endpoint = $this->buildLatestReleaseEndpointFromReleaseUrl(
                (string) config('updates.github.release_url', config('app.release.github_url', ''))
            );
        }

        if ($endpoint === '') {
            return redirect()->route('developer.version-management.index')
                ->with('error', 'GitHub latest-release endpoint is not configured.');
        }

        try {
            $response = $this->githubClient()->get($endpoint);
        } catch (ConnectionException $exception) {
            return redirect()->route('developer.version-management.index')
                ->with('error', 'Unable to connect to GitHub (SSL/cURL issue). Configure UPDATES_GITHUB_VERIFY_SSL or UPDATES_GITHUB_CA_BUNDLE for local setup.');
        } catch (\Throwable $exception) {
            return redirect()->route('developer.version-management.index')
                ->with('error', 'Unable to fetch GitHub release at this time.');
        }

        if (! $response->successful()) {
            return redirect()->route('developer.version-management.index')
                ->with('error', 'Unable to fetch GitHub release at this time.');
        }

        $payload = $response->json();
        $version = trim((string) ($payload['tag_name'] ?? ''));
        $notes = (string) ($payload['body'] ?? '');

        if ($version === '') {
            return redirect()->route('developer.version-management.index')
                ->with('error', 'GitHub response did not include a release tag.');
        }

        $existing = AppVersion::query()->where('version', $version)->first();

        if ($existing) {
            return redirect()->route('developer.version-management.index')
                ->with('success', 'GitHub release already exists in version list.');
        }

        AppVersion::query()->create([
            'version' => $version,
            'notes' => $notes !== '' ? $notes : null,
            'is_active' => false,
        ]);

        return redirect()->route('developer.version-management.index')
            ->with('success', 'Latest GitHub release added to version list.');
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

    private function githubClient(): PendingRequest
    {
        $client = Http::withHeaders([
            'User-Agent' => 'EduProfile-VersionSync',
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

    public function updateSupportRequestStatus(Request $request, SupportRequest $supportRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'resolved'])],
        ]);

        $supportRequest->update([
            'status' => $validated['status'],
        ]);

        return redirect()->route('developer.version-management.index', ['support_requests_page' => $request->input('support_requests_page')])
            ->with('success', 'Support request status updated.');
    }
}
