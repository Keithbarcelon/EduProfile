<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RoleManagementService
{
    public function createRole(int $schoolId, array $validated): Role
    {
        return DB::transaction(function () use ($schoolId, $validated): Role {
            $role = Role::create([
                'school_id' => $schoolId,
                'name' => $validated['name'],
                'slug' => $this->resolveUniqueSlug($schoolId, (string) ($validated['slug'] ?? $validated['name'])),
                'description' => $validated['description'] ?? null,
                'is_system' => false,
            ]);

            $role->permissions()->sync($validated['permission_ids'] ?? []);

            return $role->fresh(['permissions']);
        });
    }

    public function updateRole(Role $role, array $validated): Role
    {
        return DB::transaction(function () use ($role, $validated): Role {
            $slugSource = (string) ($validated['slug'] ?? $validated['name']);

            $role->update([
                'name' => $validated['name'],
                'slug' => $this->resolveUniqueSlug((int) $role->school_id, $slugSource, (int) $role->id),
                'description' => $validated['description'] ?? null,
            ]);

            $role->permissions()->sync($validated['permission_ids'] ?? []);

            return $role->fresh(['permissions']);
        });
    }

    public function deleteRole(Role $role): void
    {
        if ($role->is_system) {
            abort(422, 'System roles cannot be deleted.');
        }

        DB::transaction(function () use ($role): void {
            $role->permissions()->detach();
            $role->users()->detach();
            $role->delete();
        });
    }

    /**
     * @param list<int|string> $roleIds
     */
    public function syncUserRoles(User $user, array $roleIds, ?int $assignedBy = null): void
    {
        $validRoles = Role::query()
            ->where('school_id', $user->school_id)
            ->whereIn('id', $roleIds)
            ->get(['id', 'slug']);

        $validRoleIds = $validRoles
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $pivotData = [];
        foreach ($validRoleIds as $roleId) {
            $pivotData[$roleId] = ['assigned_by' => $assignedBy];
        }

        $user->roles()->sync($pivotData);

        $resolvedLegacyRole = $this->resolveLegacyRoleFromRbacSlugs(
            $validRoles
                ->pluck('slug')
                ->map(fn ($slug) => strtolower(str_replace('-', '_', trim((string) $slug))) ?: '')
                ->filter(fn ($slug) => $slug !== '')
                ->values()
                ->all()
        );

        if ($resolvedLegacyRole !== null && $resolvedLegacyRole !== $user->role) {
            $user->forceFill(['role' => $resolvedLegacyRole])->save();
        }
    }

    /**
     * @param list<int|string> $permissionIds
     */
    public function syncUserPermissions(User $user, array $permissionIds, ?int $assignedBy = null): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('user_permission')) {
            return;
        }

        $validPermissions = Permission::query()
            ->whereIn('id', $permissionIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $pivotData = [];
        foreach ($validPermissions as $permissionId) {
            $pivotData[$permissionId] = ['assigned_by' => $assignedBy];
        }

        $user->permissions()->sync($pivotData);
    }

    private function resolveUniqueSlug(int $schoolId, string $source, ?int $ignoreRoleId = null): string
    {
        $base = Str::slug($source);
        $base = $base !== '' ? $base : 'role';
        $slug = $base;
        $counter = 1;

        while ($this->slugExists($schoolId, $slug, $ignoreRoleId)) {
            $counter++;
            $slug = $base.'-'.$counter;
        }

        return $slug;
    }

    private function slugExists(int $schoolId, string $slug, ?int $ignoreRoleId = null): bool
    {
        return Role::query()
            ->where('school_id', $schoolId)
            ->where('slug', $slug)
            ->when($ignoreRoleId, fn ($query) => $query->whereKeyNot($ignoreRoleId))
            ->exists();
    }

    /**
     * @param list<string> $rbacRoleSlugs
     */
    private function resolveLegacyRoleFromRbacSlugs(array $rbacRoleSlugs): ?string
    {
        if ($rbacRoleSlugs === []) {
            return null;
        }

        $rolePriority = [
            'tenant_admin',
            'admin',
            'admission',
            'faculty',
            'student',
        ];

        foreach ($rolePriority as $priorityRole) {
            if (in_array($priorityRole, $rbacRoleSlugs, true)) {
                return $priorityRole === 'admin' ? 'tenant_admin' : $priorityRole;
            }
        }

        return null;
    }
}
