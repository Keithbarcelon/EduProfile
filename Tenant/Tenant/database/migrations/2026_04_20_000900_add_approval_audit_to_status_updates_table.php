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
            if (! Schema::hasColumn('status_updates', 'approval_audit')) {
                $table->json('approval_audit')->nullable()->after('required_role_slug');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('status_updates', function (Blueprint $table): void {
            if (Schema::hasColumn('status_updates', 'approval_audit')) {
                $table->dropColumn('approval_audit');
            }
        });
    }
};
