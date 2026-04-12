<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTenantSignupRequest;
use App\Models\School;
use App\Models\User;
use App\Notifications\TenantLifecycleNotification;
use App\Services\TenantDatabaseProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class TenantSignupController extends Controller
{
    public function __construct(private readonly TenantDatabaseProvisioner $databaseProvisioner)
    {
    }

    public function create(): View
    {
        return view('auth.register');
    }

    public function store(StoreTenantSignupRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $databaseName = $this->databaseProvisioner->generateUniqueDatabaseName($validated['tenant_name']);
        $domain = $validated['tenant_domain']
            ?? $this->databaseProvisioner->generateUniqueDomain($validated['tenant_name']);

        $this->databaseProvisioner->createDatabase($databaseName);

        $tenant = DB::connection('central')->transaction(function () use ($validated, $databaseName, $domain): School {
            $planStartedAt = now()->toDateString();
            $planDueAt = match ($validated['plan_type']) {
                'premium' => now()->addYear()->toDateString(),
                default => now()->addMonth()->toDateString(),
            };

            return School::on('central')->create([
                'name' => $validated['tenant_name'],
                'school_type' => 'School',
                'address' => $validated['address'],
                'email' => $validated['admin_email'],
                'contact_number' => null,
                'plan_type' => $validated['plan_type'],
                'plan_started_at' => $planStartedAt,
                'plan_due_at' => $planDueAt,
                'plan_expiration_email' => $validated['plan_expiration_email'] ?? $validated['admin_email'],
                'signup_admin_name' => $validated['signup_admin_name'],
                'tenant_domain' => $domain,
                'tenant_database' => $databaseName,
                'is_enabled' => true,
            ]);
        });

        $this->migrateTenantDatabase($databaseName);
        $this->createTenantAdminAccount($databaseName, $tenant, $validated);

        Notification::route('mail', $validated['admin_email'])
            ->notify(new TenantLifecycleNotification($tenant, 'created'));

        $reminderEmail = $validated['plan_expiration_email'] ?? null;
        if ($reminderEmail && $reminderEmail !== $validated['admin_email']) {
            Notification::route('mail', $reminderEmail)
                ->notify(new TenantLifecycleNotification($tenant, 'created'));
        }

        $loginUrl = $this->buildTenantLoginUrl($domain);

        return redirect()->away($loginUrl);
    }

    private function buildTenantLoginUrl(string $domain): string
    {
        if (app()->environment('local')) {
            $port = (int) env('TENANT_LOCAL_PORT', 8001);

            return sprintf('http://%s:%d/login', $domain, $port);
        }

        return sprintf('https://%s/login', $domain);
    }

    private function migrateTenantDatabase(string $databaseName): void
    {
        $connection = config('database.connections.mysql');
        $connection['database'] = $databaseName;

        config(['database.connections.tenant_signup' => $connection]);
        DB::purge('tenant_signup');

        Artisan::call('migrate', [
            '--database' => 'tenant_signup',
            '--force' => true,
        ]);
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function createTenantAdminAccount(string $databaseName, School $centralSchool, array $validated): void
    {
        $connection = config('database.connections.mysql');
        $connection['database'] = $databaseName;

        config(['database.connections.tenant_signup' => $connection]);
        DB::purge('tenant_signup');

        $tenantSchoolId = DB::connection('tenant_signup')
            ->table('schools')
            ->insertGetId([
                'name' => $centralSchool->name,
                'school_type' => $centralSchool->school_type,
                'address' => $centralSchool->address,
                'email' => $centralSchool->email,
                'contact_number' => $centralSchool->contact_number,
                'plan_type' => $centralSchool->plan_type,
                'plan_started_at' => optional($centralSchool->plan_started_at)->toDateString(),
                'plan_due_at' => optional($centralSchool->plan_due_at)->toDateString(),
                'plan_expiration_email' => $centralSchool->plan_expiration_email,
                'signup_admin_name' => $centralSchool->signup_admin_name,
                'tenant_domain' => $centralSchool->tenant_domain,
                'tenant_database' => $centralSchool->tenant_database,
                'is_enabled' => $centralSchool->is_enabled,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        User::on('tenant_signup')->create([
            'school_id' => $tenantSchoolId,
            'name' => $validated['signup_admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'role' => 'tenant_admin',
            'email_verified_at' => now(),
        ]);
    }
}
