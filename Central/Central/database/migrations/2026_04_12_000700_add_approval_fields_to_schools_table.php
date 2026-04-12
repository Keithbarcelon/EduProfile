<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('requested_tenant_domain')->nullable()->unique()->after('tenant_domain');
            $table->enum('approval_status', ['pending', 'approved'])->default('pending')->after('is_enabled');
            $table->timestamp('approved_at')->nullable()->after('approval_status');
        });

        DB::table('schools')
            ->whereNotNull('tenant_domain')
            ->update([
                'requested_tenant_domain' => DB::raw('tenant_domain'),
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]);

        DB::table('schools')
            ->whereNull('tenant_domain')
            ->update([
                'approval_status' => 'pending',
                'is_enabled' => false,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropUnique(['requested_tenant_domain']);
            $table->dropColumn([
                'requested_tenant_domain',
                'approval_status',
                'approved_at',
            ]);
        });
    }
};
