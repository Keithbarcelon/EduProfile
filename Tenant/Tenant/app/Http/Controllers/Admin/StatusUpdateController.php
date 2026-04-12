<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Models\StatusUpdate;
use App\Models\Student;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StatusUpdateController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $this->authorize('viewAny', StatusUpdate::class);
        $user = Auth::user();
        $schoolId = (int) app('currentSchool')->id;

        $updates = StatusUpdate::query()
            ->where('school_id', $schoolId)
            ->with(['student', 'initiator', 'approver'])
            ->when(in_array($user->role, [UserRole::DEPARTMENT->value, UserRole::FACULTY->value]), function ($query) use ($user) {
                $query->whereHas('student', function ($studentQuery) use ($user) {
                    $studentQuery->where('department_id', $user->department_id ?? 0);
                });
            })
            ->latest()
            ->paginate(15);

        $students = Student::query()
            ->where('school_id', $schoolId)
            ->with('department')
            ->when(in_array($user->role, [UserRole::DEPARTMENT->value, UserRole::FACULTY->value]), fn ($query) => $query->where('department_id', $user->department_id ?? 0))
            ->when(request()->filled('status_category'), fn ($query) => $query->where('status_category', (string) request()->input('status_category')))
            ->latest()
            ->paginate(12, ['*'], 'students_page')
            ->withQueryString();

        $statusCategoryCounts = Student::query()
            ->where('school_id', $schoolId)
            ->selectRaw('status_category, COUNT(*) as total')
            ->groupBy('status_category')
            ->pluck('total', 'status_category');

        return view('admin.status-updates.index', compact('updates', 'students', 'statusCategoryCounts'));
    }

    /**
     * Store a new status update request (primarily for Faculty).
     */
    public function store(Request $request, Student $student): RedirectResponse
    {
        $this->authorize('create', StatusUpdate::class);
        $this->authorize('view', $student);

        $validated = $request->validate([
            'new_status' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        StatusUpdate::create([
            'school_id' => (int) app('currentSchool')->id,
            'student_id' => $student->id,
            'user_id' => Auth::id(),
            'old_status' => $student->status,
            'new_status' => $validated['new_status'],
            'remarks' => $validated['remarks'],
            'approval_status' => 'pending',
        ]);

        return back()->with('success', 'Status update request submitted for approval.');
    }

    /**
     * Approve a status update (primarily for Department/Admin).
     */
    public function approve(StatusUpdate $statusUpdate): RedirectResponse
    {
        $this->authorize('approve', $statusUpdate);

        $statusUpdate->update([
            'approval_status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        // Apply the change to the student record
        $statusUpdate->student->update([
            'status' => $statusUpdate->new_status,
        ]);

        return back()->with('success', 'Status update approved.');
    }

    public function reject(StatusUpdate $statusUpdate, Request $request): RedirectResponse
    {
        $this->authorize('approve', $statusUpdate);

        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $statusUpdate->update([
            'approval_status' => 'rejected',
            'approved_by' => Auth::id(),
            'remarks' => trim((string) $statusUpdate->remarks."\nRejected: ".$validated['rejection_reason']),
        ]);

        return back()->with('success', 'Status update rejected.');
    }
}
