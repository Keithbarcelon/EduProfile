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
        Schema::create('tenant_bandwidth_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_database');
            $table->string('tenant_domain')->nullable();
            $table->date('usage_date');
            $table->unsignedBigInteger('total_bytes')->default(0);
            $table->unsignedBigInteger('request_count')->default(0);
            $table->timestamp('last_recorded_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_database', 'usage_date'], 'tbm_tenant_date_uniq');
            $table->index(['usage_date', 'tenant_database'], 'tbm_date_tenant_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_bandwidth_metrics');
    }
};
