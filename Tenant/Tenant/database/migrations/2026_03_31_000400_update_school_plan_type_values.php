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
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Expand enum temporarily to include legacy value before data remap.
            DB::statement("ALTER TABLE schools MODIFY plan_type ENUM('basic', 'pro', 'standard', 'premium') NOT NULL DEFAULT 'basic'");

            DB::table('schools')
                ->where('plan_type', 'pro')
                ->update(['plan_type' => 'standard']);

            DB::statement("ALTER TABLE schools MODIFY plan_type ENUM('basic', 'standard', 'premium') NOT NULL DEFAULT 'basic'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Expand enum temporarily to include new values before rollback remap.
            DB::statement("ALTER TABLE schools MODIFY plan_type ENUM('basic', 'pro', 'standard', 'premium') NOT NULL DEFAULT 'basic'");

            DB::table('schools')
                ->whereIn('plan_type', ['standard', 'premium'])
                ->update(['plan_type' => 'pro']);

            DB::statement("ALTER TABLE schools MODIFY plan_type ENUM('basic', 'pro') NOT NULL DEFAULT 'basic'");
        }
    }
};
