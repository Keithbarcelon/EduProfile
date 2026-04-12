<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->decimal('storage_used_mb', 12, 2)->default(0)->after('tenant_database');
            $table->decimal('bandwidth_used_mb', 12, 2)->default(0)->after('storage_used_mb');
            $table->timestamp('usage_refreshed_at')->nullable()->after('bandwidth_used_mb');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'storage_used_mb',
                'bandwidth_used_mb',
                'usage_refreshed_at',
            ]);
        });
    }
};
