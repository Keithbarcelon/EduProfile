<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Developer\StoreTenantRequest;
use App\Http\Requests\Developer\UpdateTenantSubscriptionRequest;
use App\Http\Requests\Developer\UpdateTenantRequest;
use App\Models\Plan;
use App\Models\School;
use App\Notifications\SchoolRegistrationConfirmationNotification;
use App\Notifications\TenantLifecycleNotification;
use App\Services\TenantDatabaseProvisioner;
use App\Services\TenantStatusService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use InvalidArgumentException;

class TenantController extends Controller
{
    public function __construct(
        private readonly TenantDatabaseProvisioner $databaseProvisioner,
        private readonly TenantStatusService $tenantStatusService,
    )
    {
    }

    public function index(Request $request): View
    {
        $baseQuery = School::query();

        if ($search = $request->string('search')->toString()) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('signup_admin_name', 'like', "%{$search}%")
                  ->orWhere('plan_expiration_email', 'like', "%{$search}%")
                  ->orWhere('tenant_domain', 'like', "%{$search}%")
                  ->orWhere('requested_tenant_domain', 'like', "%{$search}%")
                  ->orWhere('tenant_database', 'like', "%{$search}%");
            });
        }

        if ($plan = $request->input('plan_type')) {
            $baseQuery->where('plan_type', $plan);
        }

        $pendingQuery = clone $baseQuery;
        $enabledQuery = clone $baseQuery;
        $disabledQuery = clone $baseQuery;

        $pendingQuery->where('approval_status', School::STATUS_PENDING);
        $enabledQuery->where('approval_status', School::STATUS_APPROVED)->where('is_enabled', true);
        $disabledQuery->where('approval_status', School::STATUS_APPROVED)->where('is_enabled', false);

        $summary = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $pendingQuery)->count(),
            'enabled' => (clone $enabledQuery)->count(),
            'disabled' => (clone $disabledQuery)->count(),
        ];

        $pendingTenants = $pendingQuery->latest()->paginate(10, ['*'], 'pending_page')->withQueryString();
        $enabledTenants = $enabledQuery->latest()->paginate(10, ['*'], 'enabled_page')->withQueryString();
        $disabledTenants = $disabledQuery->latest()->paginate(10, ['*'], 'disabled_page')->withQueryString();

        return view('developer.tenants.index', compact('summary', 'pendingTenants', 'enabledTenants', 'disabledTenants'));
    }

    public function planManagement(Request $request): View
    {
        $query = School::query();

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('tenant_domain', 'like', "%{$search}%")
                    ->orWhere('requested_tenant_domain', 'like', "%{$search}%")
                    ->orWhere('signup_admin_name', 'like', "%{$search}%")
                    ->orWhere('plan_expiration_email', 'like', "%{$search}%");
            });
        }

        if ($plan = $request->input('plan_type')) {
            $query->where('plan_type', $plan);
        }

        $this->applyStatusFilter($query, $request->input('status'));

        $tenants = $query->orderBy('plan_due_at')->paginate(15)->withQueryString();
        $this->attachUsageMetrics($tenants->getCollection());

        $summary = [
            'total' => School::count(),
            'expired' => School::whereNotNull('plan_due_at')->whereDate('plan_due_at', '<', Carbon::today()->toDateString())->count(),
            'expiring30' => School::whereNotNull('plan_due_at')
                ->whereDate('plan_due_at', '>=', Carbon::today()->toDateString())
                ->whereDate('plan_due_at', '<=', Carbon::today()->addDays(30)->toDateString())
                ->count(),
            'over_limit' => $this->countOverUsageLimit(),
        ];

        $plansQuery = Plan::query()
            ->withCount(['features', 'tenantPlans']);

        if ($planSearch = $request->string('plan_search')->toString()) {
            $plansQuery->where(function ($query) use ($planSearch) {
                $query->where('name', 'like', "%{$planSearch}%")
                    ->orWhere('slug', 'like', "%{$planSearch}%")
                    ->orWhere('description', 'like', "%{$planSearch}%");
            });
        }

        if ($request->input('plan_state') === 'active') {
            $plansQuery->where('is_active', true);
        }

        if ($request->input('plan_state') === 'inactive') {
            $plansQuery->where('is_active', false);
        }

        $plans = $plansQuery->latest()->paginate(10, ['*'], 'plans_page')->withQueryString();

        $planCatalog = School::planCatalog();

        $schools = School::query()
            ->where('approval_status', 'approved')
            ->orderBy('name')
            ->get(['id', 'name', 'tenant_domain']);

        return view('developer.tenants.plan-management', compact('tenants', 'summary', 'planCatalog', 'plans', 'schools'));
    }

    public function monitoring(Request $request): View
    {
        $days = max((int) $request->input('days', 7), 1);

        $baseQuery = School::query();

        if ($search = $request->string('search')->toString()) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('tenant_domain', 'like', "%{$search}%")
                    ->orWhere('requested_tenant_domain', 'like', "%{$search}%")
                    ->orWhere('tenant_database', 'like', "%{$search}%");
            });
        }

        $this->applyStatusFilter($baseQuery, $request->input('status'));

        $monitoringQuery = clone $baseQuery;

        if ($health = $request->input('health')) {
            $today = Carbon::today()->toDateString();
            $threshold = Carbon::today()->addDays($days)->toDateString();

            if ($health === 'expired') {
                $monitoringQuery->whereNotNull('plan_due_at')->whereDate('plan_due_at', '<', $today);
            }

            if ($health === 'expiring') {
                $monitoringQuery->whereNotNull('plan_due_at')
                    ->whereDate('plan_due_at', '>=', $today)
                    ->whereDate('plan_due_at', '<=', $threshold);
            }

            if ($health === 'healthy') {
                $monitoringQuery->where(function ($q) use ($threshold) {
                    $q->whereNull('plan_due_at')
                        ->orWhereDate('plan_due_at', '>', $threshold);
                });
            }
        }

        $tenants = $monitoringQuery->latest()->paginate(15)->withQueryString();
        $this->attachUsageMetrics($tenants->getCollection());

        $summary = [
            'total' => School::count(),
            'pending' => School::where('approval_status', 'pending')->count(),
            'enabled' => School::where('approval_status', 'approved')->where('is_enabled', true)->count(),
            'disabled' => School::where('approval_status', 'approved')->where('is_enabled', false)->count(),
            'expired' => School::whereNotNull('plan_due_at')->whereDate('plan_due_at', '<', Carbon::today()->toDateString())->count(),
            'expiring' => School::whereNotNull('plan_due_at')
                ->whereDate('plan_due_at', '>=', Carbon::today()->toDateString())
                ->whereDate('plan_due_at', '<=', Carbon::today()->addDays($days)->toDateString())
                ->count(),
            'over_limit' => $this->countOverUsageLimit(),
        ];

        return view('developer.tenants.monitoring', compact('tenants', 'summary', 'days'));
    }

    public function syncUsageMetrics(): RedirectResponse
    {
        $schools = School::query()
            ->whereNotNull('tenant_database')
            ->get(['id', 'tenant_database', 'storage_used_mb', 'bandwidth_used_mb']);

        if ($schools->isEmpty()) {
            return back()->with('error', 'No tenant databases found to sync usage from.');
        }

        $storageMap = $this->fetchDatabaseStorageUsageMap(
            $schools->pluck('tenant_database')->filter()->values()->all()
        );
        $bandwidthMap = $this->fetchBandwidthUsageMapByDatabase(
            $schools->pluck('tenant_database')->filter()->values()->all()
        );

        $now = now();
        $affected = 0;

        foreach ($schools as $school) {
            $database = (string) $school->tenant_database;
            $storage = (float) ($storageMap[$database] ?? 0);
            $hasBandwidthMetric = array_key_exists($database, $bandwidthMap);
            $bandwidth = $hasBandwidthMetric
                ? (float) $bandwidthMap[$database]
                : (float) $school->bandwidth_used_mb;

            if ((float) $school->storage_used_mb !== $storage || ($hasBandwidthMetric && (float) $school->bandwidth_used_mb !== $bandwidth)) {
                $school->storage_used_mb = $storage;
                $school->bandwidth_used_mb = $bandwidth;
                $school->usage_refreshed_at = $now;
                $school->save();
                $affected++;
                continue;
            }

            $school->usage_refreshed_at = $now;
            $school->save();
        }

        return back()->with('success', "Usage sync complete. Updated: {$affected} tenant(s). Bandwidth is sourced automatically from tenant traffic metrics.");
    }

    public function updateUsage(Request $request, School $tenant): RedirectResponse
    {
        $request->merge([
            'storage_used_mb' => $this->normalizeDecimalInput($request->input('storage_used_mb')),
            'bandwidth_used_mb' => $this->normalizeDecimalInput($request->input('bandwidth_used_mb')),
        ]);

        $validated = $request->validate([
            'storage_used_mb' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'bandwidth_used_mb' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $tenant->update([
            'storage_used_mb' => round((float) $validated['storage_used_mb'], 2),
            'bandwidth_used_mb' => round((float) $validated['bandwidth_used_mb'], 2),
            'usage_refreshed_at' => now(),
        ]);

        return back()->with('success', 'Tenant usage updated successfully.');
    }

    public function extendPlan(Request $request, School $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $baseDate = $tenant->plan_due_at ? Carbon::parse($tenant->plan_due_at) : Carbon::today();

        if ($baseDate->isPast()) {
            $baseDate = Carbon::today();
        }

        $tenant->update([
            'plan_due_at' => $baseDate->addDays((int) $validated['days'])->toDateString(),
        ]);

        return back()->with('success', 'Tenant plan due date extended successfully.');
    }

    public function sendReminder(Request $request, School $tenant): RedirectResponse
    {
        if (! $tenant->plan_expiration_email) {
            return back()->with('error', 'No reminder email configured for this tenant.');
        }

        $days = max((int) $request->input('days', 7), 1);
        $eventType = $tenant->isSubscriptionExpired() ? 'subscription_expired' : 'subscription_expiring';

        if (! $this->sendLifecycleNotification($tenant, $eventType)) {
            return back()->with('error', 'Reminder was not sent due to email transport failure. Please verify SMTP credentials.');
        }

        return back()->with('success', 'Subscription reminder sent successfully.');
    }

    public function syncExpiredTenants(): RedirectResponse
    {
        $today = Carbon::today()->toDateString();

        $affected = School::query()
            ->where('is_enabled', true)
            ->whereNotNull('plan_due_at')
            ->whereDate('plan_due_at', '<', $today)
            ->update([
                'is_enabled' => false,
                'disabled_at' => now(),
                'disable_reason' => 'Plan expired',
            ]);

        return back()->with('success', "Expired tenant sync complete. Auto-disabled: {$affected}.");
    }

    public function create(): View
    {
        return view('developer.tenants.create');
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $adminEmail = $validated['admin_email'];
        $billingCycle = $validated['billing_cycle'] ?? 'monthly';
        $trialEndsAt = $this->resolveTrialEndsAt($validated['plan_started_at'] ?? null, (int) ($validated['free_trial_days'] ?? 0));

        $databaseName = $this->databaseProvisioner->generateUniqueDatabaseName($validated['name']);

        $domainInput = $validated['tenant_domain'] ?? null;
        if ($domainInput && !str_ends_with($domainInput, '.localhost')) {
            $domainInput .= '.localhost';
        }

        $tenant = DB::transaction(function () use ($validated, $databaseName, $billingCycle, $trialEndsAt, $domainInput): School {
            return School::create([
                'name' => $validated['name'],
                'school_type' => $validated['school_type'] ?? 'School',
                'address' => $validated['address'],
                'email' => $validated['admin_email'],
                'contact_number' => null,
                'plan_type' => $validated['plan_type'],
                'plan_started_at' => $validated['plan_started_at'] ?? null,
                'plan_due_at' => $validated['plan_due_at'] ?? null,
                'billing_cycle' => $billingCycle,
                'trial_ends_at' => $trialEndsAt,
                'plan_expiration_email' => $validated['plan_expiration_email'],
                'signup_admin_name' => $validated['signup_admin_name'],
                'signup_admin_password' => $validated['admin_password'],
                'tenant_domain' => null,
                'requested_tenant_domain' => $domainInput,
                'tenant_database' => $databaseName,
                'is_enabled' => false,
                'approval_status' => 'pending',
                'approved_at' => null,
            ]);
        });

        $this->sendRegistrationNotifications($tenant, $adminEmail);

        return redirect()->route('developer.tenants.index')
            ->with('success', 'Tenant created and queued for approval. Domain will be assigned after approval.');
    }

    public function show(School $tenant): View
    {
        $this->attachUsageMetrics(collect([$tenant]));

        return view('developer.tenants.show', compact('tenant'));
    }

    public function edit(School $tenant): View
    {
        return view('developer.tenants.edit', compact('tenant'));
    }

    public function update(UpdateTenantRequest $request, School $tenant): RedirectResponse
    {
        $validated = $request->validated();
        $adminEmail = $validated['admin_email'] ?? $tenant->email ?? $validated['plan_expiration_email'];
        $adminPassword = $validated['admin_password'] ?? '';
        $billingCycle = $validated['billing_cycle'] ?? ($tenant->billing_cycle ?? 'monthly');
        $trialDays = array_key_exists('free_trial_days', $validated)
            ? (int) ($validated['free_trial_days'] ?? 0)
            : ($tenant->trial_ends_at && $tenant->plan_started_at
                ? max(0, $tenant->plan_started_at->diffInDays($tenant->trial_ends_at, false))
                : 0);
        $trialEndsAt = $this->resolveTrialEndsAt($validated['plan_started_at'] ?? null, $trialDays);

        if (! $tenant->tenant_database) {
            $tenant->tenant_database = $this->databaseProvisioner->generateUniqueDatabaseName($validated['name']);
        }

        $domainInput = $validated['tenant_domain'] ?? null;
        if ($domainInput && !str_ends_with($domainInput, '.localhost')) {
            $domainInput .= '.localhost';
        }

        $isApproved = ($tenant->approval_status ?? 'pending') === 'approved';
        $nextDomain = $isApproved
            ? ($domainInput ?? $tenant->tenant_domain)
            : null;
        $nextRequestedDomain = $domainInput ?? $tenant->requested_tenant_domain;

        $tenant->update([
            'name' => $validated['name'],
            'school_type' => $validated['school_type'] ?? 'School',
            'address' => $validated['address'],
            'email' => $validated['admin_email'] ?? $tenant->email,
            'contact_number' => $tenant->contact_number,
            'plan_type' => $validated['plan_type'],
            'plan_started_at' => $validated['plan_started_at'] ?? null,
            'plan_due_at' => $validated['plan_due_at'] ?? null,
            'billing_cycle' => $billingCycle,
            'trial_ends_at' => $trialEndsAt,
            'plan_expiration_email' => $validated['plan_expiration_email'],
            'signup_admin_name' => $validated['signup_admin_name'],
            'signup_admin_password' => $adminPassword !== '' ? $adminPassword : $tenant->signup_admin_password,
            'tenant_domain' => $nextDomain,
            'requested_tenant_domain' => $nextRequestedDomain,
            'tenant_database' => $tenant->tenant_database ?? $validated['tenant_database'],
            'is_enabled' => $isApproved ? (bool) ($validated['is_enabled'] ?? false) : false,
        ]);

        if (! $tenant->isApproved()) {
            return redirect()->route('developer.tenants.index')
                ->with('success', 'Pending tenant updated successfully. Provisioning will run on approval.');
        }

        try {
            $this->databaseProvisioner->createDatabase($tenant->tenant_database);
            $this->databaseProvisioner->migrateTenantSchema($tenant->tenant_database);
            $this->databaseProvisioner->seedTenantCoreData($tenant->tenant_database, [
                'name' => $tenant->name,
                'school_type' => $tenant->school_type,
                'address' => $tenant->address,
                'email' => $tenant->email,
                'contact_number' => $tenant->contact_number,
                'plan_type' => $tenant->plan_type,
                'plan_started_at' => optional($tenant->plan_started_at)->toDateString(),
                'plan_due_at' => optional($tenant->plan_due_at)->toDateString(),
                'plan_expiration_email' => $tenant->plan_expiration_email,
                'signup_admin_name' => $tenant->signup_admin_name,
                'tenant_domain' => $tenant->isApproved() ? $tenant->tenant_domain : null,
                'is_enabled' => $tenant->isApproved() ? $tenant->is_enabled : false,
            ], [
                'name' => $tenant->signup_admin_name,
                'email' => $adminEmail,
                'password' => $adminPassword,
            ]);
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['admin_password' => $exception->getMessage()]);
        }

        return redirect()->route('developer.tenants.index')
            ->with('success', 'Tenant updated and provisioning synced successfully.');
    }

    public function approve(School $tenant): RedirectResponse
    {
        if ($tenant->isApproved()) {
            return back()->with('success', 'Tenant is already approved.');
        }

        $adminEmail = (string) ($tenant->email ?? '');
        if ($adminEmail === '') {
            return back()->with('error', 'Cannot approve tenant without an admin email. Please update tenant contact/admin email first.');
        }

        $databaseName = (string) ($tenant->tenant_database ?? '');
        if ($databaseName === '') {
            return back()->with('error', 'Cannot approve tenant without a tenant database name. Please update the tenant record first.');
        }

        $stagedAdminPassword = (string) ($tenant->signup_admin_password ?? '');

        $domain = $this->resolveDomainForApproval($tenant);

        if (
            School::query()
                ->whereKeyNot($tenant->id)
                ->where(function (Builder $query) use ($domain): void {
                    $query->where('tenant_domain', $domain)
                        ->orWhere('requested_tenant_domain', $domain);
                })
                ->exists()
        ) {
            return back()->with('error', 'Requested domain is already in use. Please edit the tenant and provide a different preferred domain before approval.');
        }

        try {
            $this->databaseProvisioner->createDatabase($databaseName);
            $this->databaseProvisioner->migrateTenantSchema($databaseName);
            $this->databaseProvisioner->seedTenantCoreData($databaseName, [
                'name' => $tenant->name,
                'school_type' => $tenant->school_type,
                'address' => $tenant->address,
                'email' => $tenant->email,
                'contact_number' => $tenant->contact_number,
                'plan_type' => $tenant->plan_type,
                'plan_started_at' => optional($tenant->plan_started_at)->toDateString(),
                'plan_due_at' => optional($tenant->plan_due_at)->toDateString(),
                'plan_expiration_email' => $tenant->plan_expiration_email,
                'signup_admin_name' => $tenant->signup_admin_name,
                'tenant_domain' => $domain,
                'is_enabled' => true,
            ], [
                'name' => $tenant->signup_admin_name,
                'email' => $adminEmail,
                'password' => $stagedAdminPassword,
            ]);

            $tenant->update([
                'tenant_domain' => $domain,
                'requested_tenant_domain' => $domain,
                'approval_status' => School::STATUS_APPROVED,
                'approved_at' => now(),
                'is_enabled' => true,
                'signup_admin_password' => null,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Tenant approval provisioning failed.', [
                'tenant_id' => $tenant->id,
                'tenant_database' => $databaseName,
                'domain' => $domain,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('error', $exception->getMessage());
        }

        $this->notifyTenantApproval($tenant, $adminEmail);

        return back()->with('success', "Tenant approved. Domain activated: {$tenant->tenant_domain}");
    }

    public function reject(School $tenant): RedirectResponse
    {
        if ($tenant->isApproved()) {
            return back()->with('error', 'Approved tenants cannot be rejected. Disable or delete instead.');
        }

        $validated = request()->validate([
            'rejection_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $tenantName = (string) $tenant->name;
        $reason = trim((string) ($validated['rejection_reason'] ?? ''));

        $tenant->delete();

        $message = "Pending tenant \"{$tenantName}\" was rejected and removed.";

        if ($reason !== '') {
            $message .= " Reason: {$reason}";
        }

        return redirect()->route('developer.tenants.index')->with('success', $message);
    }

    public function updateSubscription(UpdateTenantSubscriptionRequest $request, School $tenant): RedirectResponse
    {
        $validated = $request->validated();

        $tenant->update([
            'plan_type' => $validated['plan_type'],
            'plan_started_at' => $validated['plan_started_at'],
            'plan_due_at' => $validated['plan_due_at'],
            'billing_cycle' => $validated['billing_cycle'],
            'trial_ends_at' => $this->resolveTrialEndsAt($validated['plan_started_at'], (int) ($validated['free_trial_days'] ?? 0)),
            'plan_expiration_email' => $validated['plan_expiration_email'],
        ]);

        $message = 'Tenant subscription updated successfully.';

        if ($tenant->plan_expiration_email && ! $this->sendLifecycleNotification($tenant, 'subscription_updated')) {
            $message .= ' Subscription was saved, but email notification failed.';
        }

        return redirect()->route('developer.tenants.show', $tenant)
            ->with('success', $message);
    }

    public function destroy(School $tenant): RedirectResponse
    {
        $tenant->delete();

        return redirect()->route('developer.tenants.index')
            ->with('success', 'Tenant removed from central app. Tenant database was not dropped.');
    }

    public function toggleStatus(School $tenant): RedirectResponse
    {
        if (! $tenant->isApproved()) {
            return redirect()->route('developer.tenants.index')
                ->with('error', 'Pending tenants cannot be enabled or disabled until approved.');
        }

        $disableReason = null;

        if ($tenant->is_enabled) {
            $validated = request()->validate([
                'disable_reason' => ['required', 'string', 'max:255'],
            ]);

            $disableReason = $validated['disable_reason'];
        }

        try {
            $tenant = $this->tenantStatusService->toggle($tenant, $disableReason);
        } catch (InvalidArgumentException $exception) {
            return redirect()->route('developer.tenants.index')->with('error', $exception->getMessage());
        }

        $message = $tenant->is_enabled ? 'Tenant enabled.' : 'Tenant disabled.';

        if ($tenant->plan_expiration_email && ! $this->sendLifecycleNotification($tenant, $tenant->is_enabled ? 'enabled' : 'disabled')) {
            $message .= ' Status changed, but email notification failed.';
        }

        return redirect()->route('developer.tenants.index')
            ->with('success', $message);
    }

    private function sendLifecycleNotification(School $tenant, string $eventType): bool
    {
        if (! $tenant->plan_expiration_email) {
            return false;
        }

        try {
            Notification::route('mail', $tenant->plan_expiration_email)
                ->notify(new TenantLifecycleNotification($tenant, $eventType));

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Tenant lifecycle notification failed.', [
                'tenant_id' => $tenant->id,
                'tenant_email' => $tenant->plan_expiration_email,
                'event_type' => $eventType,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function notifyTenantApproval(School $tenant, string $adminEmail): void
    {
        $this->sendRegistrationNotifications($tenant, $adminEmail);
    }

    private function sendRegistrationNotifications(School $tenant, string $adminEmail): void
    {
        try {
            Notification::route('mail', $adminEmail)
                ->notify(new SchoolRegistrationConfirmationNotification(
                    $tenant,
                    (string) $tenant->signup_admin_name,
                    $adminEmail
                ));

            if ($tenant->plan_expiration_email && $tenant->plan_expiration_email !== $adminEmail) {
                Notification::route('mail', $tenant->plan_expiration_email)
                    ->notify(new SchoolRegistrationConfirmationNotification(
                        $tenant,
                        (string) $tenant->signup_admin_name,
                        $adminEmail
                    ));
            }
        } catch (\Throwable $exception) {
            Log::warning('Tenant registration notification failed.', [
                'tenant_id' => $tenant->id,
                'admin_email' => $adminEmail,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function resolveDomainForApproval(School $tenant): string
    {
        $domain = $tenant->requested_tenant_domain ?: $tenant->tenant_domain;

        if (! $domain) {
            return $this->databaseProvisioner->generateUniqueDomain($tenant->name);
        }

        return str_ends_with($domain, '.localhost') ? $domain : $domain.'.localhost';
    }

    private function applyStatusFilter(Builder $query, ?string $status): void
    {
        if (! $status) {
            return;
        }

        if ($status === 'pending') {
            $query->where('approval_status', 'pending');

            return;
        }

        if ($status === 'approved') {
            $query->where('approval_status', 'approved');

            return;
        }

        if ($status === 'enabled') {
            $query->where('approval_status', 'approved')->where('is_enabled', true);

            return;
        }

        if ($status === 'disabled') {
            $query->where('approval_status', 'approved')->where('is_enabled', false);
        }
    }

    private function countOverUsageLimit(): int
    {
        return School::query()
            ->get(['plan_type', 'storage_used_mb', 'bandwidth_used_mb'])
            ->filter(fn (School $school) => $school->isOverUsageLimit())
            ->count();
    }

    private function attachUsageMetrics(Collection $schools): void
    {
        $databaseNames = $schools
            ->pluck('tenant_database')
            ->filter()
            ->values()
            ->all();

        if ($databaseNames === []) {
            return;
        }

        $storageMap = $this->fetchDatabaseStorageUsageMap($databaseNames);
        $bandwidthMap = $this->fetchBandwidthUsageMapByDatabase($databaseNames);

        $schools->each(function (School $school) use ($storageMap, $bandwidthMap): void {
            $database = (string) $school->tenant_database;

            if ($database !== '' && array_key_exists($database, $storageMap)) {
                $school->storage_used_mb = (float) $storageMap[$database];
            }

            if ($database !== '' && array_key_exists($database, $bandwidthMap)) {
                $school->bandwidth_used_mb = (float) $bandwidthMap[$database];
            }
        });
    }

    private function fetchDatabaseStorageUsageMap(array $databaseNames): array
    {
        $driver = DB::getDriverName();

        if ($driver !== 'mysql' || $databaseNames === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($databaseNames), '?'));

        $rows = DB::select(
            "SELECT table_schema AS tenant_database, ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS storage_mb
             FROM information_schema.tables
             WHERE table_schema IN ({$placeholders})
             GROUP BY table_schema",
            $databaseNames
        );

        $usage = [];

        foreach ($rows as $row) {
            $usage[$row->tenant_database] = (float) $row->storage_mb;
        }

        return $usage;
    }

    private function resolveTrialEndsAt(?string $planStartedAt, int $trialDays): ?string
    {
        if (! $planStartedAt || $trialDays <= 0) {
            return null;
        }

        return Carbon::parse($planStartedAt)->addDays($trialDays)->toDateString();
    }

    private function fetchBandwidthUsageMapByDatabase(array $databaseNames): array
    {
        if ($databaseNames === [] || ! Schema::hasTable('tenant_bandwidth_metrics')) {
            return [];
        }

        $days = max((int) config('services.tenant_usage.bandwidth_window_days', 30), 1);
        $windowStart = now()->subDays($days - 1)->toDateString();

        $rows = DB::table('tenant_bandwidth_metrics')
            ->selectRaw('tenant_database, ROUND(SUM(total_bytes) / 1024 / 1024, 2) AS bandwidth_mb')
            ->whereIn('tenant_database', $databaseNames)
            ->whereDate('usage_date', '>=', $windowStart)
            ->groupBy('tenant_database')
            ->get();

        $usage = [];

        foreach ($rows as $row) {
            $usage[$row->tenant_database] = (float) $row->bandwidth_mb;
        }

        return $usage;
    }

    private function normalizeDecimalInput(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        // Accept values entered with locale separators like "1,234.56" or "123,45".
        $normalized = str_replace(' ', '', trim($value));

        if (str_contains($normalized, ',') && ! str_contains($normalized, '.')) {
            return str_replace(',', '.', $normalized);
        }

        return str_replace(',', '', $normalized);
    }
}
