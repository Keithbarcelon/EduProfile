<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = $this->currentSchoolId();

        $users = User::query()
            ->where('school_id', $schoolId)
            ->whereIn('role', $this->manageableRoles())
            ->with(['department', 'student'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->string('search'));

                $query->where(function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', (string) $request->input('role')))
            ->when($request->filled('department_id'), fn ($query) => $query->where('department_id', $request->integer('department_id')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $departments = Department::query()
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get();

        return view('admin.users.index', [
            'users' => $users,
            'departments' => $departments,
            'roles' => $this->manageableRoles(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'departments' => Department::query()
                ->where('school_id', $this->currentSchoolId())
                ->orderBy('name')
                ->get(),
            'roles' => $this->manageableRoles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in($this->manageableRoles())],
            'department_id' => [
                Rule::requiredIf(fn () => $request->input('role') === UserRole::FACULTY->value),
                'nullable',
                'integer',
                Rule::exists('departments', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
        ]);

        $user = User::create([
            'school_id' => $schoolId,
            'department_id' => $validated['department_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        $this->syncLegacyRoleMapping($user, $validated['role']);

        $message = $validated['role'] === UserRole::STUDENT->value
            ? 'Student user created. Link this account from the Students page to complete the student profile.'
            : 'User created successfully.';

        return redirect()->route('admin.users.index')
            ->with('success', $message);
    }

    public function edit(User $user): View
    {
        $this->ensureManageable($user);

        return view('admin.users.edit', [
            'userModel' => $user,
            'departments' => Department::query()
                ->where('school_id', $this->currentSchoolId())
                ->orderBy('name')
                ->get(),
            'roles' => $this->manageableRoles(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureManageable($user);
        $schoolId = $this->currentSchoolId();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in($this->manageableRoles())],
            'department_id' => [
                Rule::requiredIf(fn () => $request->input('role') === UserRole::FACULTY->value),
                'nullable',
                'integer',
                Rule::exists('departments', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
        ]);

        $payload = [
            'school_id' => $schoolId,
            'department_id' => $validated['department_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);
        $this->syncLegacyRoleMapping($user, $validated['role']);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->ensureManageable($user);

        if ($user->is(auth()->user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * @return list<string>
     */
    private function manageableRoles(): array
    {
        return [
            UserRole::ADMISSION->value,
            UserRole::DEPARTMENT->value,
            UserRole::FACULTY->value,
            UserRole::STUDENT->value,
        ];
    }

    private function ensureManageable(User $user): void
    {
        abort_unless(in_array($user->role, $this->manageableRoles(), true), 404);
    }

    private function currentSchoolId(): int
    {
        return (int) app('currentSchool')->id;
    }

    private function syncLegacyRoleMapping(User $user, ?string $legacyRole): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('user_role')) {
            return;
        }

        $map = [
            'admin' => 'tenant-admin',
            'tenant_admin' => 'tenant-admin',
            'admission' => 'admission',
            'department' => 'department',
            'faculty' => 'faculty',
            'student' => 'student',
        ];

        $targetSlug = $map[$legacyRole ?? ''] ?? null;

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
            $role->id => ['assigned_by' => auth()->id()],
        ]);
    }
}
