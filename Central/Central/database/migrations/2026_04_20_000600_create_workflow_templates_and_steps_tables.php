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
        Schema::create('workflow_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('module_key')->default('status_monitoring');
            $table->string('workflow_key');
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['school_id', 'module_key', 'workflow_key']);
        });

        Schema::create('workflow_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workflow_template_id')->constrained('workflow_templates')->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->string('step_name')->nullable();
            $table->string('role_slug');
            $table->json('rules_json')->nullable();
            $table->unsignedInteger('sla_hours')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['workflow_template_id', 'step_order']);
            $table->index(['workflow_template_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflow_templates');
    }
};
