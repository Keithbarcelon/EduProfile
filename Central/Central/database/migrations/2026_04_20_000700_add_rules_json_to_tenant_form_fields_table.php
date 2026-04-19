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
        Schema::table('tenant_form_fields', function (Blueprint $table): void {
            if (! Schema::hasColumn('tenant_form_fields', 'rules_json')) {
                $table->json('rules_json')->nullable()->after('options_json');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_form_fields', function (Blueprint $table): void {
            if (Schema::hasColumn('tenant_form_fields', 'rules_json')) {
                $table->dropColumn('rules_json');
            }
        });
    }
};
