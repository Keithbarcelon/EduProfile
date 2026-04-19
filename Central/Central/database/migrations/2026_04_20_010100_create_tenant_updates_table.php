<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_updates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('schools')->cascadeOnDelete();
            $table->string('current_version', 120);
            $table->timestamp('last_checked_at')->nullable();
            $table->string('latest_seen_version', 120)->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique('tenant_id');
            $table->index('latest_seen_version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_updates');
    }
};
