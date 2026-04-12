<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
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

        $driver = DB::connection('central')->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if ($driver !== 'mysql') {
            throw new InvalidArgumentException('Tenant database provisioning supports only MySQL in this setup.');
        }

        $escapedDatabase = str_replace('`', '``', $databaseName);
        DB::connection('central')->statement("CREATE DATABASE IF NOT EXISTS `{$escapedDatabase}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
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
        } while (DB::connection('central')->table('schools')->where('tenant_database', $candidate)->exists());

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
        } while (DB::connection('central')->table('schools')->where('tenant_domain', $candidate)->exists());

        return $candidate;
    }
}
