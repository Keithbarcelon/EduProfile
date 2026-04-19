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
        if (Schema::hasTable('tenant_document_requirements')) {
            return;
        }

        Schema::create('tenant_document_requirements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('module_key')->default('documents');
            $table->string('status_category')->nullable();
            $table->string('document_name');
            $table->boolean('is_required')->default(true);
            $table->json('rules_json')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'module_key', 'status_category', 'is_active'], 'tdr_school_module_status_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_document_requirements');
    }
};
