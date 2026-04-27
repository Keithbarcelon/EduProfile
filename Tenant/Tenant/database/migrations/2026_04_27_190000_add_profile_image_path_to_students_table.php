<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('students', 'profile_image_path')) {
            Schema::table('students', function (Blueprint $table): void {
                $table->string('profile_image_path')->nullable()->after('current_status_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('students', 'profile_image_path')) {
            Schema::table('students', function (Blueprint $table): void {
                $table->dropColumn('profile_image_path');
            });
        }
    }
};
