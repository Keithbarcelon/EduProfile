<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Document;
use App\Models\Report;
use App\Models\Student;
use App\Enums\UserRole;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Database\Eloquent\Builder;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Report::class);
        $schoolId = (int) app('currentSchool')->id;
        $user = $request->user();
        $departmentScopedRole = in_array($user->role, [UserRole::DEPARTMENT->value, UserRole::FACULTY->value], true);
        $departmentId = $user->department_id;

        $query = Student::query()
            ->where('school_id', $schoolId)
            ->when($departmentScopedRole, function ($studentQuery) use ($departmentId): void {
                if ($departmentId === null) {
                    $studentQuery->whereRaw('1 = 0');

                    return;
                }

                $studentQuery->where('department_id', $departmentId);
            });

        $studentStats = [
            'total' => $query->count(),
            'by_status' => (clone $query)->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
            'by_category' => (clone $query)->selectRaw('status_category, count(*) as count')->groupBy('status_category')->pluck('count', 'status_category'),
        ];

        $departmentReports = Department::query()
            ->where('school_id', $schoolId)
            ->when($departmentScopedRole && $departmentId !== null, fn ($departmentQuery) => $departmentQuery->where('id', $departmentId))
            ->when($departmentScopedRole && $departmentId === null, fn ($departmentQuery) => $departmentQuery->whereRaw('1 = 0'))
            ->withCount([
                'students',
                'users as faculty_count' => fn ($departmentQuery) => $departmentQuery->where('role', 'faculty'),
            ])
            ->orderBy('name')
            ->get();

        $documentCompliance = [
            'total_students' => (clone $query)->count(),
            'students_with_documents' => (clone $query)->has('documents')->count(),
            'missing_submissions' => (clone $query)->doesntHave('documents')->count(),
            'by_status' => Document::query()
                ->where('school_id', $schoolId)
                ->when($departmentScopedRole, function ($documentQuery) use ($departmentId): void {
                    $documentQuery->whereHas('student', function ($studentQuery) use ($departmentId): void {
                        if ($departmentId === null) {
                            $studentQuery->whereRaw('1 = 0');

                            return;
                        }

                        $studentQuery->where('department_id', $departmentId);
                    });
                })
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ];

        return view('admin.reports.index', compact('studentStats', 'departmentReports', 'documentCompliance'));
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewFull', Report::class);

        $schoolId = (int) app('currentSchool')->id;
        $user = $request->user();
        $reportType = (string) $request->input('report_type', 'status');
        $filename = "{$reportType}-report-".now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($schoolId, $reportType, $user) {
            $handle = fopen('php://output', 'w');

            match ($reportType) {
                'department' => $this->writeDepartmentReport($handle, $schoolId, $user),
                'documents' => $this->writeDocumentReport($handle, $schoolId, $user),
                default => $this->writeStatusReport($handle, $schoolId, $user),
            };

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function writeStatusReport($handle, int $schoolId, $user): void
    {
        fputcsv($handle, ['Status Category', 'Total Students']);

        $query = Student::query()
            ->where('school_id', $schoolId);

        $this->applyDepartmentScope($query, $user);

        $query
            ->selectRaw('status_category, COUNT(*) as total')
            ->groupBy('status_category')
            ->orderBy('status_category')
            ->get()
            ->each(fn ($row) => fputcsv($handle, [$row->status_category, $row->total]));
    }

    private function writeDepartmentReport($handle, int $schoolId, $user): void
    {
        fputcsv($handle, ['Department', 'Students', 'Faculty']);

        $query = Department::query()
            ->where('school_id', $schoolId);

        if (in_array($user->role, [UserRole::DEPARTMENT->value, UserRole::FACULTY->value], true)) {
            if ($user->department_id === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('id', $user->department_id);
            }
        }

        $query
            ->withCount([
                'students',
                'users as faculty_count' => fn ($query) => $query->where('role', 'faculty'),
            ])
            ->orderBy('name')
            ->get()
            ->each(fn ($department) => fputcsv($handle, [
                $department->name,
                $department->students_count,
                $department->faculty_count,
            ]));
    }

    private function writeDocumentReport($handle, int $schoolId, $user): void
    {
        fputcsv($handle, ['Student', 'Department', 'Document Count', 'Latest Review Status']);

        $query = Student::query()
            ->where('school_id', $schoolId);

        $this->applyDepartmentScope($query, $user);

        $query
            ->with(['department', 'documents' => fn ($query) => $query->latest()])
            ->orderBy('last_name')
            ->get()
            ->each(function ($student) use ($handle) {
                fputcsv($handle, [
                    $student->full_name,
                    $student->department->name ?? 'Unassigned',
                    $student->documents->count(),
                    $student->documents->first()?->status ?? 'missing',
                ]);
            });
    }

    private function applyDepartmentScope(Builder $query, $user): void
    {
        if (! in_array($user->role, [UserRole::DEPARTMENT->value, UserRole::FACULTY->value], true)) {
            return;
        }

        if ($user->department_id === null) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where('department_id', $user->department_id);
    }
}
