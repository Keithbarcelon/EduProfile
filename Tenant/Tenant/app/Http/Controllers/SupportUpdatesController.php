<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use App\Models\School;
use App\Models\SupportRequest;
use App\Models\TenantUpdate;
use App\Services\UpdateCheckerService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class SupportUpdatesController extends Controller
{
    public function __construct(private readonly UpdateCheckerService $updateCheckerService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $school = app('currentSchool');

        abort_unless($school instanceof School, 404);

        $centralTenantId = $this->updateCheckerService->resolveCentralSchoolId($school);
        $currentVersion = (string) ($school->version ?: config('app.version', 'v1.0.0'));

        $latestInfo = $this->updateCheckerService->getLatestVersionInfo();
        $latestVersion = $latestInfo['version'];
        $moduleError = null;

        $tenantUpdate = null;
        $supportRequests = new LengthAwarePaginator([], 0, 10);

        if ($centralTenantId) {
            try {
                $tenantUpdate = TenantUpdate::query()->where('tenant_id', $centralTenantId)->first();

                $supportRequests = SupportRequest::query()
                    ->where('tenant_id', $centralTenantId)
                    ->latest('created_at')
                    ->paginate(10, ['*'], 'requests_page')
                    ->withQueryString();
            } catch (QueryException) {
                $moduleError = 'Support and Updates tables are not ready yet. Run central migrations and refresh.';
            }
        }

        $effectiveCurrentVersion = $tenantUpdate?->current_version ?: $currentVersion;
        $updateAvailable = $this->updateCheckerService->isUpdateAvailable($effectiveCurrentVersion, $latestVersion);

        return view('support-updates.index', [
            'school' => $school,
            'isTenantAdmin' => in_array($user?->role, ['admin', 'tenant_admin'], true),
            'tenantUpdate' => $tenantUpdate,
            'currentVersion' => $effectiveCurrentVersion,
            'latestVersion' => $latestVersion,
            'latestSource' => $latestInfo['source'],
            'updateAvailable' => $updateAvailable,
            'supportRequests' => $supportRequests,
            'releaseNotes' => $this->safeReleaseNotes(),
            'moduleError' => $moduleError,
        ]);
    }

    public function check(Request $request): RedirectResponse
    {
        $school = app('currentSchool');
        abort_unless($school instanceof School, 404);

        try {
            $result = $this->updateCheckerService->checkForUpdates($school);
        } catch (\RuntimeException $exception) {
            return redirect()->route('support-updates.index')
                ->with('error', $exception->getMessage());
        }

        $message = $result['update_available']
            ? 'Update available: '.$result['latest_version'].' (source: '.$result['source'].').'
            : 'No new update. Tenant is up to date.';

        return redirect()->route('support-updates.index')
            ->with('success', $message);
    }

    public function acknowledge(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'tenant_admin'], true), 403);

        $school = app('currentSchool');
        abort_unless($school instanceof School, 404);

        $this->updateCheckerService->acknowledgeUpdate($school);

        return redirect()->route('support-updates.index')
            ->with('success', 'Update notice acknowledged.');
    }

    public function syncLatest(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'tenant_admin'], true), 403);

        $school = app('currentSchool');
        abort_unless($school instanceof School, 404);

        try {
            $result = $this->updateCheckerService->syncCurrentVersionToLatest($school);
        } catch (\RuntimeException $exception) {
            return redirect()->route('support-updates.index')
                ->with('error', $exception->getMessage());
        }

        return redirect()->route('support-updates.index')
            ->with('success', 'Current tenant version synced to '.$result['version'].' (source: '.$result['source'].').');
    }

    public function checkJson(Request $request): JsonResponse
    {
        $school = app('currentSchool');
        abort_unless($school instanceof School, 404);

        try {
            $result = $this->updateCheckerService->checkForUpdates($school);
        } catch (\RuntimeException $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        $tenantUpdate = $result['tenant_update'];
        $isUpdateAvailable = (bool) $result['update_available'];

        return response()->json([
            'ok' => true,
            'message' => $isUpdateAvailable
                ? 'Update available: '.$result['latest_version'].' (source: '.$result['source'].').' 
                : 'No new update. Tenant is up to date.',
            'currentVersion' => (string) $tenantUpdate->current_version,
            'latestVersion' => $result['latest_version'] ?: null,
            'latestSource' => (string) $result['source'],
            'updateAvailable' => $isUpdateAvailable,
            'lastCheckedAt' => optional($tenantUpdate->last_checked_at)->format('M d, Y h:i A'),
        ]);
    }

    public function storeRequest(Request $request): RedirectResponse
    {
        $school = app('currentSchool');

        abort_unless($school instanceof School, 404);
        abort_unless(in_array($request->user()?->role, ['admin', 'tenant_admin'], true), 403);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $centralTenantId = $this->updateCheckerService->resolveCentralSchoolId($school);

        if (! $centralTenantId) {
            return redirect()->route('support-updates.index')
                ->with('error', 'Unable to submit bug report: tenant is not mapped to central.');
        }

        SupportRequest::query()->create([
            'tenant_id' => $centralTenantId,
            'user_id' => $request->user()?->id,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'open',
        ]);

        return redirect()->route('support-updates.index')
            ->with('success', 'Bug report submitted successfully. The central team will review and respond to your report.');
    }

    private function safeReleaseNotes()
    {
        try {
            return AppVersion::query()
                ->orderByDesc('created_at')
                ->limit(8)
                ->get();
        } catch (QueryException) {
            return collect();
        }
    }
}
