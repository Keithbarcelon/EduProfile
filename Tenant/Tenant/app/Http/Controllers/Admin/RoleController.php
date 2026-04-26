<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RoleManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(private readonly RoleManagementService $roleManagementService)
    {
    }

    public function index(): View|RedirectResponse
    {
        if (! $this->rbacTablesAvailable()) {
            return $this->missingRbacTablesResponse();
        }

        $schoolId = $this->currentSchoolId();

        $roles = Role::query()
            ->where('school_id', $schoolId)
            ->withCount(['permissions', 'users'])
            ->with('permissions:id,name,slug,module')
            ->orderBy('name')
            ->paginate(12);

        return view('admin.roles.index', [
            'roles' => $roles,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        if (! $this->rbacTablesAvailable()) {
            return $this->missingRbacTablesResponse();
        }

        return view('admin.roles.create', [
            'permissions' => $this->permissionsByModule(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $this->rbacTablesAvailable()) {
            return $this->missingRbacTablesResponse();
        }

        $validated = $this->validateRole($request);

        $this->roleManagementService->createRole($this->currentSchoolId(), $validated);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View|RedirectResponse
    {
        if (! $this->rbacTablesAvailable()) {
            return $this->missingRbacTablesResponse();
        }

        $this->ensureRoleInCurrentSchool($role);

        return view('admin.roles.edit', [
            'roleModel' => $role->load('permissions:id'),
            'permissions' => $this->permissionsByModule(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if (! $this->rbacTablesAvailable()) {
            return $this->missingRbacTablesResponse();
        }

        $this->ensureRoleInCurrentSchool($role);
        $validated = $this->validateRole($request, $role);

        $this->roleManagementService->updateRole($role, $validated);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (! $this->rbacTablesAvailable()) {
            return $this->missingRbacTablesResponse();
        }

        $this->ensureRoleInCurrentSchool($role);

        $this->roleManagementService->deleteRole($role);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRole(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => [
                'integer',
                Rule::exists('permissions', 'id'),
            ],
        ]);
    }

    private function permissionsByModule(): \Illuminate\Support\Collection
    {
        if (! $this->rbacTablesAvailable()) {
            return collect();
        }

        return Permission::query()
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => $permission->module ?: 'General');
    }

    private function rbacTablesAvailable(): bool
    {
        return Schema::hasTable('roles')
            && Schema::hasTable('permissions')
            && Schema::hasTable('role_permission')
            && Schema::hasTable('user_role');
    }

    private function missingRbacTablesResponse(): RedirectResponse
    {
        return redirect()->route('admin.dashboard')
            ->with('error', 'Role management is unavailable until the tenant RBAC tables are migrated.');
    }

    private function ensureRoleInCurrentSchool(Role $role): void
    {
        abort_unless((int) $role->school_id === $this->currentSchoolId(), 404);
    }

    private function currentSchoolId(): int
    {
        return (int) app('currentSchool')->id;
    }
}
