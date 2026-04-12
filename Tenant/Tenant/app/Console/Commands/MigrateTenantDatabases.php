<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MigrateTenantDatabases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate {tenant? : Tenant database name, domain, or central school ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run pending migrations against one tenant database or all tenant databases.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tenantArgument = trim((string) $this->argument('tenant'));

        $tenants = DB::connection('central')
            ->table('schools')
            ->select('id', 'name', 'tenant_domain', 'tenant_database')
            ->whereNotNull('tenant_database')
            ->when($tenantArgument !== '', function ($query) use ($tenantArgument) {
                $query->where(function ($tenantQuery) use ($tenantArgument) {
                    $tenantQuery->where('tenant_database', $tenantArgument)
                        ->orWhere('tenant_domain', $tenantArgument);

                    if (ctype_digit($tenantArgument)) {
                        $tenantQuery->orWhere('id', (int) $tenantArgument);
                    }
                });
            })
            ->orderBy('id')
            ->get();

        if ($tenants->isEmpty()) {
            $this->error('No matching tenant databases found.');

            return self::FAILURE;
        }

        foreach ($tenants as $tenant) {
            $this->info(sprintf(
                'Migrating tenant [%s] %s (%s)',
                $tenant->id,
                $tenant->name,
                $tenant->tenant_database
            ));

            $this->migrateTenantDatabase((string) $tenant->tenant_database);
        }

        $this->info('Tenant migration run complete.');

        return self::SUCCESS;
    }

    private function migrateTenantDatabase(string $databaseName): void
    {
        $connection = config('database.connections.mysql');
        $connection['database'] = $databaseName;

        config(['database.connections.tenant_migration' => $connection]);
        DB::purge('tenant_migration');

        Artisan::call('migrate', [
            '--database' => 'tenant_migration',
            '--force' => true,
        ]);

        $this->line(Artisan::output());
    }
}
