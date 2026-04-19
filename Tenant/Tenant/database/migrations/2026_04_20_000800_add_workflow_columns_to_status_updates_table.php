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
        Schema::table('status_updates', function (Blueprint $table): void {
            if (! Schema::hasColumn('status_updates', 'workflow_key')) {
                $table->string('workflow_key')->nullable()->after('approval_status');
            }

            if (! Schema::hasColumn('status_updates', 'workflow_step_order')) {
                $table->unsignedInteger('workflow_step_order')->nullable()->after('workflow_key');
            }

            if (! Schema::hasColumn('status_updates', 'required_role_slug')) {
                $table->string('required_role_slug')->nullable()->after('workflow_step_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('status_updates', function (Blueprint $table): void {
            if (Schema::hasColumn('status_updates', 'required_role_slug')) {
                $table->dropColumn('required_role_slug');
            }

            if (Schema::hasColumn('status_updates', 'workflow_step_order')) {
                $table->dropColumn('workflow_step_order');
            }

            if (Schema::hasColumn('status_updates', 'workflow_key')) {
                $table->dropColumn('workflow_key');
            }
        });
    }
};
