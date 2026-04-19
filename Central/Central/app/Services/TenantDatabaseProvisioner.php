<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

class TenantDatabaseProvisioner
{
    /**
     * Create tenant database when it does not exist.
     */
    public function createDatabase(string $databaseName): void
    {
        if (! preg_match('/^[A-Za-z0-9_]+$/', $databaseName)) {
            throw new InvalidArgumentException('Invalid tenant database name.');
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if ($driver !== 'mysql') {
            throw new InvalidArgumentException('Tenant database provisioning supports only MySQL in this setup.');
        }

        $escapedDatabase = str_replace('`', '``', $databaseName);
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$escapedDatabase}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    /**
     * Run tenant migrations against the provisioned tenant database.
     */
    public function migrateTenantSchema(string $databaseName): void
    {
        $connectionName = $this->configureTenantConnection($databaseName);
        $tenantMigrationPath = $this->resolveTenantMigrationPath();

        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }

        Artisan::call('migrate', [
            '--database' => $connectionName,
            '--path' => $tenantMigrationPath,
            '--realpath' => true,
            '--force' => true,
        ]);

        DB::purge($connectionName);
    }

    private function resolveTenantMigrationPath(): string
    {
        $candidatePaths = [
            base_path('../../Tenant/Tenant/database/migrations'),
            base_path('../Tenant/Tenant/database/migrations'),
        ];

        foreach ($candidatePaths as $candidatePath) {
            $resolved = realpath($candidatePath);

            if ($resolved !== false && is_dir($resolved)) {
                return $resolved;
            }
        }

        throw new InvalidArgumentException('Tenant migration path could not be resolved.');
    }

    /**
     * Seed/refresh core tenant records (school + admin account) in tenant DB.
     *
     * @param array<string, mixed> $school
     * @param array<string, mixed> $admin
     */
    public function seedTenantCoreData(string $databaseName, array $school, array $admin): void
    {
        $connectionName = $this->configureTenantConnection($databaseName);
        $conn = DB::connection($connectionName);
        $now = Carbon::now();

        $existingSchool = $conn->table('schools')
            ->where('tenant_database', $databaseName)
            ->first();

        $schoolPayload = [
            'name' => (string) ($school['name'] ?? 'Tenant School'),
            'school_type' => (string) ($school['school_type'] ?? 'School'),
            'address' => (string) ($school['address'] ?? ''),
            'email' => $school['email'] ?? null,
            'contact_number' => $school['contact_number'] ?? null,
            'plan_type' => (string) ($school['plan_type'] ?? 'basic'),
            'plan_started_at' => $school['plan_started_at'] ?? null,
            'plan_due_at' => $school['plan_due_at'] ?? null,
            'plan_expiration_email' => $school['plan_expiration_email'] ?? null,
            'signup_admin_name' => (string) ($school['signup_admin_name'] ?? ''),
            'tenant_domain' => (string) ($school['tenant_domain'] ?? ''),
            'tenant_database' => $databaseName,
            'is_enabled' => (bool) ($school['is_enabled'] ?? true),
            'updated_at' => $now,
        ];

        if ($existingSchool) {
            $schoolId = (int) $existingSchool->id;
            $conn->table('schools')->where('id', $schoolId)->update($schoolPayload);
        } else {
            $schoolId = (int) $conn->table('schools')->insertGetId(array_merge($schoolPayload, [
                'created_at' => $now,
            ]));
        }

        $adminEmail = (string) ($admin['email'] ?? '');
        $adminPassword = (string) ($admin['password'] ?? '');
        $existingAdmin = $adminEmail !== ''
            ? $conn->table('users')->where('email', $adminEmail)->first()
            : null;

        if ($adminEmail === '') {
            throw new InvalidArgumentException('Tenant admin email is required for tenant provisioning.');
        }

        $userPayload = [
            'school_id' => $schoolId,
            'name' => (string) ($admin['name'] ?? 'Tenant Admin'),
            'role' => 'tenant_admin',
            'email_verified_at' => $now,
            'remember_token' => null,
            'updated_at' => $now,
        ];

        if ($adminPassword !== '') {
            $userPayload['password'] = Hash::make($adminPassword);
        } elseif (! $existingAdmin) {
            throw new InvalidArgumentException('Tenant admin password is required when creating a new tenant admin account.');
        }

        if ($existingAdmin) {
            $conn->table('users')->where('id', (int) $existingAdmin->id)->update($userPayload);
        } else {
            $conn->table('users')->insert(array_merge($userPayload, [
                'email' => $adminEmail,
                'created_at' => $now,
            ]));
        }

        DB::purge($connectionName);
    }

    private function configureTenantConnection(string $databaseName): string
    {
        $connectionName = 'tenant_provision';
        $connection = config('database.connections.mysql');

        if (! is_array($connection)) {
            throw new InvalidArgumentException('MySQL connection is not configured correctly.');
        }

        $connection['database'] = $databaseName;

        config(["database.connections.{$connectionName}" => $connection]);
        DB::purge($connectionName);

        return $connectionName;
    }

    /**
     * Build a unique db name from tenant name when manual name is not provided.
     */
    public function generateUniqueDatabaseName(string $tenantName): string
    {
        $base = Str::of($tenantName)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->limit(30, '')
            ->value();

        if ($base === '') {
            $base = 'tenant';
        }

        do {
            $candidate = sprintf('tenant_%s_%s', $base, Str::lower(Str::random(6)));
        } while (School::where('tenant_database', $candidate)->exists());

        return $candidate;
    }

    /**
     * Build a unique tenant domain from tenant name when manual domain is not provided.
     */
    public function generateUniqueDomain(string $tenantName): string
    {
        $base = Str::of($tenantName)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->limit(40, '')
            ->value();

        if ($base === '') {
            $base = 'tenant';
        }

        $suffix = config('app.tenant_base_domain', env('TENANT_BASE_DOMAIN', 'localhost'));

        do {
            $candidate = sprintf('%s-%s.%s', $base, Str::lower(Str::random(4)), $suffix);
        } while (School::where('tenant_domain', $candidate)->exists());

        return $candidate;
    }
}
