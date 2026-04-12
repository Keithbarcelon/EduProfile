<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $schoolId = $this->currentSchoolId();

        $departments = Department::query()
            ->where('school_id', $schoolId)
            ->with(['users' => fn ($query) => $query->where('role', UserRole::FACULTY->value)])
            ->withCount([
                'students',
                'users as faculty_count' => fn ($query) => $query->where('role', UserRole::FACULTY->value),
            ])
            ->orderBy('name')
            ->get();

        return view('admin.departments.index', [
            'departments' => $departments,
            'facultyMembers' => $this->facultyOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.departments.create', [
            'facultyMembers' => $this->facultyOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('departments', 'code')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'description' => ['nullable', 'string'],
            'faculty_ids' => ['nullable', 'array'],
            'faculty_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('school_id', $schoolId)
                    ->where('role', UserRole::FACULTY->value)),
            ],
        ]);

        $department = Department::create([
            'school_id' => $schoolId,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        $this->syncFaculty($department, $validated['faculty_ids'] ?? []);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department): View
    {
        return view('admin.departments.edit', [
            'department' => $department,
            'facultyMembers' => $this->facultyOptions(),
            'selectedFacultyIds' => $department->users()
                ->where('role', UserRole::FACULTY->value)
                ->pluck('users.id')
                ->all(),
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($department->id),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('departments', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($department->id),
            ],
            'description' => ['nullable', 'string'],
            'faculty_ids' => ['nullable', 'array'],
            'faculty_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('school_id', $schoolId)
                    ->where('role', UserRole::FACULTY->value)),
            ],
        ]);

        $department->update([
            'school_id' => $schoolId,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        $this->syncFaculty($department, $validated['faculty_ids'] ?? []);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted successfully.');
    }

    private function syncFaculty(Department $department, array $facultyIds): void
    {
        $schoolId = $this->currentSchoolId();

        User::query()
            ->where('school_id', $schoolId)
            ->where('role', UserRole::FACULTY->value)
            ->where('department_id', $department->id)
            ->whereNotIn('id', $facultyIds ?: [0])
            ->update(['department_id' => null]);

        if ($facultyIds !== []) {
            User::query()
                ->where('school_id', $schoolId)
                ->where('role', UserRole::FACULTY->value)
                ->whereIn('id', $facultyIds)
                ->update(['department_id' => $department->id]);
        }
    }

    private function facultyOptions()
    {
        return User::query()
            ->where('school_id', $this->currentSchoolId())
            ->where('role', UserRole::FACULTY->value)
            ->orderBy('name')
            ->get();
    }

    private function currentSchoolId(): int
    {
        return (int) app('currentSchool')->id;
    }
}
