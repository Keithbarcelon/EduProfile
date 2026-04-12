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
            $table->enum('plan_type', ['basic', 'pro'])->default('basic')->after('contact_number');
            $table->date('plan_started_at')->nullable()->after('plan_type');
            $table->date('plan_due_at')->nullable()->after('plan_started_at');
            $table->string('plan_expiration_email')->nullable()->after('plan_due_at');
            $table->string('signup_admin_name')->nullable()->after('plan_expiration_email');
            $table->string('tenant_database')->nullable()->unique()->after('signup_admin_name');
            $table->boolean('is_enabled')->default(true)->after('tenant_database');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropUnique(['tenant_database']);
            $table->dropColumn([
                'plan_type',
                'plan_started_at',
                'plan_due_at',
                'plan_expiration_email',
                'signup_admin_name',
                'tenant_database',
                'is_enabled',
            ]);
        });
    }
};
