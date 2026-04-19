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
        $moduleId = DB::table('modules')
            ->where('key', 'support_updates')
            ->value('id');

        if (! $moduleId) {
            return;
        }

        DB::table('modules')
            ->where('id', $moduleId)
            ->update([
                'default_enabled' => true,
                'updated_at' => now(),
            ]);

        DB::table('tenant_modules')
            ->where('module_id', $moduleId)
            ->update([
                'is_enabled' => true,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep support_updates enabled by default once normalized.
    }
};
