<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionDefinitions = [
            ['name' => 'Manage Students', 'slug' => 'manage_students', 'module' => 'Students', 'description' => 'Create, edit, and manage student records'],
            ['name' => 'View Reports', 'slug' => 'view_reports', 'module' => 'Reports', 'description' => 'View and export reports'],
            ['name' => 'Manage Users', 'slug' => 'manage_users', 'module' => 'Users', 'description' => 'Create, edit, and remove user accounts'],
            ['name' => 'Manage Departments', 'slug' => 'manage_departments', 'module' => 'Departments', 'description' => 'Create and maintain department records'],
            ['name' => 'Manage Settings', 'slug' => 'manage_settings', 'module' => 'Settings', 'description' => 'Update tenant settings and preferences'],
            ['name' => 'Manage Status Updates', 'slug' => 'manage_status_updates', 'module' => 'Status Monitoring', 'description' => 'Submit, review, and approve student status updates'],
            ['name' => 'Review Documents', 'slug' => 'review_documents', 'module' => 'Document Reviews', 'description' => 'Review and process student document submissions'],
            ['name' => 'Manage Profiles', 'slug' => 'manage_profiles', 'module' => 'Users', 'description' => 'Manage users, departments, and settings'],
            ['name' => 'Manage Tenant', 'slug' => 'manage_tenant', 'module' => 'Tenant', 'description' => 'Manage tenant operational controls'],
            ['name' => 'Manage Roles', 'slug' => 'manage_roles', 'module' => 'RBAC', 'description' => 'Create roles and assign permissions'],
            ['name' => 'Manage Support', 'slug' => 'manage_support', 'module' => 'Support', 'description' => 'Create and process support tickets'],
        ];

        foreach ($permissionDefinitions as $definition) {
            Permission::updateOrCreate(
                ['slug' => $definition['slug']],
                $definition,
            );
        }

        $rolePermissionMap = [
            'tenant-admin' => ['manage_students', 'view_reports', 'manage_users', 'manage_departments', 'manage_settings', 'manage_status_updates', 'review_documents', 'manage_profiles', 'manage_tenant', 'manage_roles', 'manage_support'],
            'admission' => ['manage_students', 'view_reports', 'manage_status_updates', 'review_documents'],
            'faculty' => ['manage_students', 'manage_status_updates', 'review_documents'],
            'student' => [],
        ];

        School::query()->each(function (School $school) use ($rolePermissionMap): void {
            foreach ($rolePermissionMap as $slug => $permissionSlugs) {
                $role = Role::updateOrCreate(
                    ['school_id' => $school->id, 'slug' => $slug],
                    [
                        'name' => str($slug)->replace('-', ' ')->title()->toString(),
                        'description' => 'System role',
                        'is_system' => true,
                    ]
                );

                $permissionIds = Permission::query()
                    ->whereIn('slug', $permissionSlugs)
                    ->pluck('id')
                    ->all();

                $role->permissions()->sync($permissionIds);
            }
        });

        $legacyToRole = [
            'admin' => 'tenant-admin',
            'tenant_admin' => 'tenant-admin',
            'admission' => 'admission',
            'faculty' => 'faculty',
            'student' => 'student',
        ];

        User::query()->whereNotNull('school_id')->each(function (User $user) use ($legacyToRole): void {
            $targetSlug = $legacyToRole[$user->role] ?? null;

            if (! $targetSlug) {
                return;
            }

            $role = Role::query()
                ->where('school_id', $user->school_id)
                ->where('slug', $targetSlug)
                ->first();

            if (! $role) {
                return;
            }

            $user->roles()->syncWithoutDetaching([
                $role->id => ['assigned_by' => null],
            ]);
        });
    }
}
