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
        Schema::create('tenant_modules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->json('config_json')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'module_id']);
            $table->index(['school_id', 'is_enabled']);
        });

        Schema::create('tenant_feature_flags', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('flag_key');
            $table->boolean('is_active')->default(false);
            $table->json('meta_json')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'flag_key']);
            $table->index(['school_id', 'is_active']);
        });

        Schema::create('tenant_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('setting_key');
            $table->longText('setting_value')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'setting_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
        Schema::dropIfExists('tenant_feature_flags');
        Schema::dropIfExists('tenant_modules');
    }
};
