<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\RoleManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserRoleController extends Controller
{
    public function __construct(private readonly RoleManagementService $roleManagementService)
    {
    }

    public function index(Request $request): View|RedirectResponse
    {
        if (! $this->rbacTablesAvailable()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Role assignments are unavailable until the tenant RBAC tables are migrated.');
        }

        $schoolId = $this->currentSchoolId();
        $supportsDirectPermissions = Schema::hasTable('permissions') && Schema::hasTable('user_permission');

        $usersQuery = User::query()
            ->where('school_id', $schoolId)
            ->with(['roles:id,name,slug'])
            ->orderBy('name');

        if ($supportsDirectPermissions) {
            $usersQuery->with(['permissions:id,name,slug,module']);
        }

        $users = $usersQuery->paginate(12)->withQueryString();

        $roles = Role::query()
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $permissions = $supportsDirectPermissions
            ? Permission::query()
                ->orderBy('module')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'module'])
            : collect();

        return view('admin.roles.user-assignments', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
            'supportsDirectPermissions' => $supportsDirectPermissions,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();
        abort_unless((int) $user->school_id === $schoolId, 404);

        if (! $this->rbacTablesAvailable()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Role assignments are unavailable until the tenant RBAC tables are migrated.');
        }

        $rules = [
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => [
                'integer',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer'],
        ];

        $validated = $request->validate($rules);

        $this->roleManagementService->syncUserRoles(
            $user,
            $validated['role_ids'] ?? [],
            (int) $request->user()->id,
        );

        $this->roleManagementService->syncUserPermissions(
            $user,
            $validated['permission_ids'] ?? [],
            (int) $request->user()->id,
        );

        return back()->with('success', 'User access updated successfully.');
    }

    private function currentSchoolId(): int
    {
        return (int) app('currentSchool')->id;
    }

    private function rbacTablesAvailable(): bool
    {
        return Schema::hasTable('roles') && Schema::hasTable('user_role');
    }
}
