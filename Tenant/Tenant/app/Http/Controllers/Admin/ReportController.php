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
                'students as regular_count' => fn ($q) => $q->where('status_category', 'regular'),
                'students as affirmative_count' => fn ($q) => $q->where('status_category', 'affirmative'),
                'students as probation_count' => fn ($q) => $q->where('status_category', 'probation'),
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

    public function print(Request $request): View
    {
        $this->authorize('viewAny', Report::class);
        $school = app('currentSchool');
        $user = $request->user();
        
        $students = Student::query()
            ->where('school_id', $school->id)
            ->with(['department', 'documents'])
            ->when(in_array($user->role, [UserRole::DEPARTMENT->value, UserRole::FACULTY->value], true), function ($q) use ($user) {
                return $q->where('department_id', $user->department_id);
            })
            ->orderBy('last_name')
            ->get();

        $departments = Department::query()
            ->where('school_id', $school->id)
            ->withCount([
                'students',
                'users as faculty_count' => fn($q) => $q->where('role', 'faculty')
            ])
            ->get();

        return view('admin.reports.print', compact('school', 'students', 'departments', 'user'));
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
        fputcsv($handle, ['Student Name', 'Email', 'Status Category', 'Current Status', 'Department', 'Joined Date']);

        $query = Student::query()
            ->where('school_id', $schoolId)
            ->with('department');

        $this->applyDepartmentScope($query, $user);

        $query->orderBy('last_name')
            ->get()
            ->each(fn ($student) => fputcsv($handle, [
                $student->full_name,
                $student->email,
                ucfirst($student->status_category),
                $student->status,
                $student->department->name ?? 'Unassigned',
                $student->created_at?->format('Y-m-d') ?? 'N/A',
            ]));
    }

    private function writeDepartmentReport($handle, int $schoolId, $user): void
    {
        fputcsv($handle, ['Department Name', 'Code', 'Total Students', 'Regular Students', 'Affirmative Students', 'Probation Students', 'Faculty Count']);

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
                'students as regular_count' => fn ($q) => $query->where('status_category', 'regular'),
                'students as affirmative_count' => fn ($q) => $query->where('status_category', 'affirmative'),
                'students as probation_count' => fn ($q) => $query->where('status_category', 'probation'),
                'users as faculty_count' => fn ($query) => $query->where('role', 'faculty'),
            ])
            ->orderBy('name')
            ->get()
            ->each(fn ($dept) => fputcsv($handle, [
                $dept->name,
                $dept->code ?? 'N/A',
                $dept->students_count,
                $dept->regular_count,
                $dept->affirmative_count,
                $dept->probation_count,
                $dept->faculty_count,
            ]));
    }

    private function writeDocumentReport($handle, int $schoolId, $user): void
    {
        fputcsv($handle, ['Student Name', 'Department', 'Document Type', 'Status', 'Submitted At', 'Last Updated']);

        $query = Student::query()
            ->where('school_id', $schoolId);

        $this->applyDepartmentScope($query, $user);

        $query
            ->with(['department', 'documents'])
            ->orderBy('last_name')
            ->get()
            ->each(function ($student) use ($handle) {
                if ($student->documents->isEmpty()) {
                    fputcsv($handle, [
                        $student->full_name,
                        $student->department->name ?? 'Unassigned',
                        'N/A',
                        'Missing',
                        'N/A',
                        'N/A',
                    ]);
                } else {
                    foreach ($student->documents as $doc) {
                        fputcsv($handle, [
                            $student->full_name,
                            $student->department->name ?? 'Unassigned',
                            $doc->document_type ?? 'Document',
                            ucfirst($doc->status),
                            $doc->created_at?->format('Y-m-d H:i') ?? 'N/A',
                            $doc->updated_at?->format('Y-m-d H:i') ?? 'N/A',
                        ]);
                    }
                }
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
