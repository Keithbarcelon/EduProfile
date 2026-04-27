<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $schoolId = (int) app('currentSchool')->id;
        $user = Auth::user();
        $departmentScopedRole = in_array($user->role, [UserRole::DEPARTMENT->value, UserRole::FACULTY->value], true);
        $departmentId = $user->department_id;

        $students = Student::query()
            ->where('school_id', $schoolId)
            ->when($departmentScopedRole, function ($query) use ($departmentId): void {
                if ($departmentId === null) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('department_id', $departmentId);
            });

        $totalStudents = (clone $students)->count();
        $newThisMonth = (clone $students)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $pendingDocuments = Document::query()
            ->where('school_id', $schoolId)
            ->when($departmentScopedRole, function ($query) use ($departmentId): void {
                $query->whereHas('student', function ($studentQuery) use ($departmentId): void {
                    if ($departmentId === null) {
                        $studentQuery->whereRaw('1 = 0');

                        return;
                    }

                    $studentQuery->where('department_id', $departmentId);
                });
            })
            ->where('status', 'pending')
            ->count();
        $totalUsers = User::query()
            ->where('school_id', $schoolId)
            ->when($departmentScopedRole, fn ($query) => $query->where('department_id', $departmentId))
            ->count();
        $totalDepartments = Department::query()
            ->where('school_id', $schoolId)
            ->when($departmentScopedRole && $departmentId !== null, fn ($query) => $query->where('id', $departmentId))
            ->when($departmentScopedRole && $departmentId === null, fn ($query) => $query->whereRaw('1 = 0'))
            ->count();
        $missingSubmissions = Student::query()
            ->where('school_id', $schoolId)
            ->when($departmentScopedRole, function ($query) use ($departmentId): void {
                if ($departmentId === null) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('department_id', $departmentId);
            })
            ->doesntHave('documents')
            ->count();

        $courseBreakdown = Student::query()
            ->where('school_id', $schoolId)
            ->when($departmentScopedRole, function ($query) use ($departmentId): void {
                if ($departmentId === null) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('department_id', $departmentId);
            })
            ->selectRaw('course, COUNT(*) as total')
            ->groupBy('course')
            ->orderByDesc('total')
            ->get();

        $statusCounts = Student::query()
            ->where('school_id', $schoolId)
            ->when($departmentScopedRole, function ($query) use ($departmentId): void {
                if ($departmentId === null) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('department_id', $departmentId);
            })
            ->selectRaw('status_category, COUNT(*) as total')
            ->groupBy('status_category')
            ->pluck('total', 'status_category');

        $documentStatusCounts = Document::query()
            ->where('school_id', $schoolId)
            ->when($departmentScopedRole, function ($query) use ($departmentId): void {
                $query->whereHas('student', function ($studentQuery) use ($departmentId): void {
                    if ($departmentId === null) {
                        $studentQuery->whereRaw('1 = 0');

                        return;
                    }

                    $studentQuery->where('department_id', $departmentId);
                });
            })
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $recentStudents = Student::query()
            ->where('school_id', $schoolId)
            ->when($departmentScopedRole, function ($query) use ($departmentId): void {
                if ($departmentId === null) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('department_id', $departmentId);
            })
            ->with('department')
            ->latest()
            ->limit(8)
            ->get();

        $widgetVisibility = [
            'overview_cards' => true,
            'status_overview' => true,
            'document_queue' => true,
        ];

        return view('admin.dashboard', compact(
            'totalStudents',
            'newThisMonth',
            'pendingDocuments',
            'totalUsers',
            'totalDepartments',
            'missingSubmissions',
            'courseBreakdown',
            'statusCounts',
            'documentStatusCounts',
            'recentStudents',
            'widgetVisibility'
        ));
    }
}
