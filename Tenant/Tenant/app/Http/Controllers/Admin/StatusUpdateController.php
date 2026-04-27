<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Models\Status;
use App\Models\StudentStatusHistory;
use App\Models\StatusUpdate;
use App\Models\Student;
use App\Support\StudentStatusRules;
use App\Support\TenantConfig;
use Illuminate\Support\Facades\Schema;
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

        $scopedStudents = Student::query()
            ->where('school_id', $schoolId)
            ->when(in_array($user->role, [UserRole::DEPARTMENT->value, UserRole::FACULTY->value], true), function ($query) use ($user): void {
                if ($user->department_id === null) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('department_id', $user->department_id);
            });

        $students = (clone $scopedStudents)
            ->with('department')
            ->when(request()->filled('status_category'), fn ($query) => $query->where('status_category', (string) request()->input('status_category')))
            ->latest()
            ->paginate(12, ['*'], 'students_page')
            ->withQueryString();

        $statusCategoryCounts = (clone $scopedStudents)
            ->selectRaw('status_category, COUNT(*) as total')
            ->groupBy('status_category')
            ->pluck('total', 'status_category');

        $allowedStatuses = collect();
        if (Schema::hasTable('statuses')) {
            $allowedStatusNames = StudentStatusRules::allowedStatusNamesForRole((string) $user->role);

            $allowedStatuses = Status::query()
                ->whereIn('name', $allowedStatusNames)
                ->orderByRaw("CASE name WHEN 'regular' THEN 1 WHEN 'affirmative' THEN 2 WHEN 'probation' THEN 3 ELSE 99 END")
                ->get(['id', 'name'])
                ->map(fn (Status $status): array => [
                    'id' => (int) $status->id,
                    'name' => strtolower((string) $status->name),
                    'label' => ucfirst((string) $status->name),
                ])
                ->values();
        }

        $statusChangeStudents = (clone $scopedStudents)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'student_id', 'first_name', 'middle_name', 'last_name', 'suffix'])
            ->map(fn (Student $student): array => [
                'id' => (int) $student->id,
                'student_id' => (string) $student->student_id,
                'full_name' => (string) $student->full_name,
            ])
            ->values();

        $historyEntries = collect();
        if (Schema::hasTable('student_status_history')) {
            $historyEntries = StudentStatusHistory::query()
                ->with(['student.department', 'changer'])
                ->whereHas('student', function ($studentQuery) use ($schoolId, $user): void {
                    $studentQuery->where('school_id', $schoolId)
                        ->when(in_array($user->role, [UserRole::DEPARTMENT->value, UserRole::FACULTY->value], true), function ($query) use ($user): void {
                            if ($user->department_id === null) {
                                $query->whereRaw('1 = 0');

                                return;
                            }

                            $query->where('department_id', $user->department_id);
                        });
                })
                ->latest('created_at')
                ->paginate(15, ['*'], 'history_page')
                ->withQueryString();
        }

        return view('admin.status-updates.index', compact('students', 'statusCategoryCounts', 'allowedStatuses', 'statusChangeStudents', 'historyEntries'));
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

        $workflowKey = 'status_change_approval';
        $steps = collect(TenantConfig::workflowSteps($workflowKey))->sortBy('step_order')->values();
        $firstStep = $steps->first();

        $initiatorRole = strtolower((string) (Auth::user()?->role ?? ''));
        if ($initiatorRole === 'admin') {
            $initiatorRole = 'tenant_admin';
        }

        StatusUpdate::create([
            'school_id' => (int) app('currentSchool')->id,
            'student_id' => $student->id,
            'user_id' => Auth::id(),
            'old_status' => $student->status,
            'new_status' => $validated['new_status'],
            'remarks' => $validated['remarks'],
            'approval_status' => 'pending',
            'workflow_key' => $workflowKey,
            'workflow_step_order' => $firstStep['step_order'] ?? 1,
            'required_role_slug' => $firstStep['role_slug'] ?? null,
            'approval_audit' => [[
                'action' => 'submitted',
                'at' => now()->toIso8601String(),
                'by_user_id' => Auth::id(),
                'by_role' => $initiatorRole,
                'from_step' => null,
                'to_step' => (int) ($firstStep['step_order'] ?? 1),
                'next_required_role' => (string) ($firstStep['role_slug'] ?? ''),
            ]],
        ]);

        return back()->with('success', 'Status update request submitted for approval.');
    }

    /**
     * Approve a status update (primarily for Department/Admin).
     */
    public function approve(StatusUpdate $statusUpdate): RedirectResponse
    {
        $this->authorize('approve', $statusUpdate);

        $userRole = strtolower((string) (Auth::user()?->role ?? ''));
        if ($userRole === 'admin') {
            $userRole = 'tenant_admin';
        }

        $requiredRole = strtolower((string) ($statusUpdate->required_role_slug ?? ''));
        if ($requiredRole !== '' && $userRole !== $requiredRole) {
            return back()->with('error', 'This request is currently assigned to another approval role.');
        }

        $workflowKey = (string) ($statusUpdate->workflow_key ?: 'status_change_approval');
        $currentStepOrder = (int) ($statusUpdate->workflow_step_order ?? 1);
        $steps = collect(TenantConfig::workflowSteps($workflowKey))->sortBy('step_order')->values();
        $nextStep = $steps->first(fn (array $step): bool => (int) ($step['step_order'] ?? 0) > $currentStepOrder);
        $auditLog = collect((array) $statusUpdate->approval_audit);

        if ($nextStep) {
            $auditLog->push([
                'action' => 'step_approved',
                'at' => now()->toIso8601String(),
                'by_user_id' => Auth::id(),
                'by_role' => $userRole,
                'from_step' => $currentStepOrder,
                'to_step' => (int) $nextStep['step_order'],
                'next_required_role' => (string) ($nextStep['role_slug'] ?? ''),
            ]);

            $statusUpdate->update([
                'approval_status' => 'pending',
                'workflow_step_order' => (int) $nextStep['step_order'],
                'required_role_slug' => (string) ($nextStep['role_slug'] ?? ''),
                'approval_audit' => $auditLog->values()->all(),
            ]);

            return back()->with('success', 'Status update moved to next workflow step.');
        }

        $auditLog->push([
            'action' => 'final_approved',
            'at' => now()->toIso8601String(),
            'by_user_id' => Auth::id(),
            'by_role' => $userRole,
            'from_step' => $currentStepOrder,
            'to_step' => null,
            'next_required_role' => null,
        ]);

        $statusUpdate->update([
            'approval_status' => 'approved',
            'approved_by' => Auth::id(),
            'required_role_slug' => null,
            'approval_audit' => $auditLog->values()->all(),
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

        $userRole = strtolower((string) ($request->user()?->role ?? ''));
        if ($userRole === 'admin') {
            $userRole = 'tenant_admin';
        }

        $auditLog = collect((array) $statusUpdate->approval_audit);
        $auditLog->push([
            'action' => 'rejected',
            'at' => now()->toIso8601String(),
            'by_user_id' => Auth::id(),
            'by_role' => $userRole,
            'from_step' => (int) ($statusUpdate->workflow_step_order ?? 1),
            'to_step' => null,
            'reason' => trim((string) $validated['rejection_reason']),
        ]);

        $statusUpdate->update([
            'approval_status' => 'rejected',
            'approved_by' => Auth::id(),
            'required_role_slug' => null,
            'approval_audit' => $auditLog->values()->all(),
            'remarks' => trim((string) $statusUpdate->remarks."\nRejected: ".$validated['rejection_reason']),
        ]);

        return back()->with('success', 'Status update rejected.');
    }
}
