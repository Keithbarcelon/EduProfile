<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentProfileService;
use App\Support\TenantConfig;
use App\Enums\UserRole;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Illuminate\View\View;

class StudentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly StudentProfileService $studentProfileService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Student::class);
        $user = $request->user();
        $schoolId = (int) app('currentSchool')->id;

        $students = Student::query()
            ->where('school_id', $schoolId)
            ->with(['department', 'user'])
            ->when(in_array($user->role, [UserRole::DEPARTMENT->value, UserRole::FACULTY->value]), function ($query) use ($user) {
                $query->where('department_id', $user->department_id ?? 0);
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('student_id', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->department_id, fn($q, $id) => $q->where('department_id', $id))
            ->when($request->status_category, fn($q, $cat) => $q->where('status_category', $cat))
            ->latest()
            ->paginate(15);

        $departments = Department::query()
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get();

        $overview = [
            'total' => Student::query()->where('school_id', $schoolId)->count(),
            'regular' => Student::query()->where('school_id', $schoolId)->where('status_category', 'regular')->count(),
            'affirmative' => Student::query()->where('school_id', $schoolId)->where('status_category', 'affirmative')->count(),
            'probation' => Student::query()->where('school_id', $schoolId)->where('status_category', 'probation')->count(),
        ];

        $unlinkedStudentUsers = User::query()
            ->where('school_id', $schoolId)
            ->where('role', UserRole::STUDENT->value)
            ->whereDoesntHave('student')
            ->with('department')
            ->latest()
            ->limit(12)
            ->get();

        return view('admin.students.index', compact('students', 'departments', 'overview', 'unlinkedStudentUsers'));
    }

    public function linkUser(Request $request, User $user): RedirectResponse
    {
        $this->authorize('create', Student::class);

        $schoolId = (int) app('currentSchool')->id;

        if ((int) $user->school_id !== $schoolId) {
            abort(404);
        }

        try {
            $result = $this->studentProfileService->linkUserToStudent($user, $schoolId);
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.students.edit', $result['student'])
            ->with('success', $result['message']);
    }

    public function show(Student $student): View
    {
        $this->authorize('view', $student);

        $relations = ['department', 'user', 'remarks.user', 'documents', 'statusUpdates'];

        if (Schema::hasTable('student_custom_field_values')) {
            $relations[] = 'customFieldValues';
        }

        $student->load($relations);
        $customFieldDefinitions = TenantConfig::studentCustomFields();
        $profileSections = collect(TenantConfig::profileSections())
            ->keyBy('section_key')
            ->all();

        $customFieldDefinitionsBySection = collect($customFieldDefinitions)
            ->groupBy(fn (array $field) => (string) ($field['section'] ?? 'custom_fields'))
            ->all();

        $customFieldValueMap = $student->customFieldValueMap();

        return view('admin.students.show', compact('student', 'customFieldDefinitions', 'customFieldDefinitionsBySection', 'customFieldValueMap', 'profileSections'));
    }

    public function edit(Student $student): View
    {
        $this->authorize('update', $student);
        $schoolId = (int) app('currentSchool')->id;

        if (Schema::hasTable('student_custom_field_values')) {
            $student->load('customFieldValues');
        }

        $departments = Department::query()
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get();

        $studentUsers = User::query()
            ->where('school_id', $schoolId)
            ->where('role', UserRole::STUDENT->value)
            ->where(function ($query) use ($student) {
                $query->where('id', $student->user_id)
                    ->orWhereDoesntHave('student');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $customFieldDefinitions = TenantConfig::studentCustomFields();
        $profileSections = collect(TenantConfig::profileSections())
            ->keyBy('section_key')
            ->all();

        $customFieldDefinitionsBySection = collect($customFieldDefinitions)
            ->groupBy(fn (array $field) => (string) ($field['section'] ?? 'custom_fields'))
            ->all();

        $customFieldValueMap = $student->customFieldValueMap();

        return view('admin.students.edit', compact('student', 'departments', 'studentUsers', 'customFieldDefinitions', 'customFieldDefinitionsBySection', 'customFieldValueMap', 'profileSections'));
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $this->authorize('update', $student);

        $validated = $request->validated();
        $schoolId = (int) app('currentSchool')->id;

        $this->studentProfileService->updateStudentProfile($student, $validated, $schoolId);

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Student record updated successfully.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorize('delete', $student);

        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Student record deleted. User account remains managed in User Management.');
    }

}
