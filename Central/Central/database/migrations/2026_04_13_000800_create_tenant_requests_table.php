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
        Schema::create('tenant_requests', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_name');
            $table->string('address', 500);
            $table->enum('plan_type', ['basic', 'standard', 'premium'])->default('basic');
            $table->date('plan_started_at')->nullable();
            $table->date('plan_due_at')->nullable();
            $table->string('signup_admin_name');
            $table->string('admin_email');
            $table->text('admin_password');
            $table->string('plan_expiration_email')->nullable();
            $table->string('requested_tenant_domain')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('submitted_ip', 45)->nullable();
            $table->string('submitted_user_agent', 500)->nullable();
            $table->timestamps();

            $table->index('admin_email');
            $table->index('requested_tenant_domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_requests');
    }
};
