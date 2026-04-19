<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $coreKeys = ['students', 'status_monitoring', 'documents', 'reports', 'users', 'roles'];

        DB::table('modules')
            ->whereIn('key', $coreKeys)
            ->update([
                'is_core' => true,
                'default_enabled' => true,
                'updated_at' => now(),
            ]);

        $coreIds = DB::table('modules')
            ->whereIn('key', $coreKeys)
            ->pluck('id')
            ->all();

        if ($coreIds !== []) {
            DB::table('tenant_modules')
                ->whereIn('module_id', $coreIds)
                ->update([
                    'is_enabled' => true,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep backbone modules as core once promoted.
    }
};
