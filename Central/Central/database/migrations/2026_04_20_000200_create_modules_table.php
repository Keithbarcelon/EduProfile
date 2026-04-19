<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('category')->default('general');
            $table->boolean('is_core')->default(false);
            $table->boolean('default_enabled')->default(true);
            $table->json('config_schema_json')->nullable();
            $table->timestamps();
        });

        DB::table('modules')->insert([
            ['key' => 'students', 'name' => 'Students', 'category' => 'core', 'is_core' => true, 'default_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'status_monitoring', 'name' => 'Status Monitoring', 'category' => 'student_lifecycle', 'is_core' => false, 'default_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'documents', 'name' => 'Documents', 'category' => 'student_lifecycle', 'is_core' => false, 'default_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'reports', 'name' => 'Reports', 'category' => 'analytics', 'is_core' => false, 'default_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'users', 'name' => 'Users', 'category' => 'administration', 'is_core' => false, 'default_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'roles', 'name' => 'Roles and Assignments', 'category' => 'administration', 'is_core' => false, 'default_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'departments', 'name' => 'Departments', 'category' => 'administration', 'is_core' => false, 'default_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'settings', 'name' => 'Settings', 'category' => 'administration', 'is_core' => false, 'default_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'support_updates', 'name' => 'Support and Updates', 'category' => 'platform', 'is_core' => false, 'default_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
