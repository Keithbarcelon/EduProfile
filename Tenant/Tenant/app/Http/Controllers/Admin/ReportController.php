<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Document;
use App\Models\Report;
use App\Models\Student;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Report::class);
        $schoolId = (int) app('currentSchool')->id;

        $query = Student::query()->where('school_id', $schoolId);

        $studentStats = [
            'total' => $query->count(),
            'by_status' => (clone $query)->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
            'by_category' => (clone $query)->selectRaw('status_category, count(*) as count')->groupBy('status_category')->pluck('count', 'status_category'),
        ];

        $departmentReports = Department::query()
            ->where('school_id', $schoolId)
            ->withCount([
                'students',
                'users as faculty_count' => fn ($departmentQuery) => $departmentQuery->where('role', 'faculty'),
            ])
            ->orderBy('name')
            ->get();

        $documentCompliance = [
            'total_students' => Student::query()->where('school_id', $schoolId)->count(),
            'students_with_documents' => Student::query()->where('school_id', $schoolId)->has('documents')->count(),
            'missing_submissions' => Student::query()->where('school_id', $schoolId)->doesntHave('documents')->count(),
            'by_status' => Document::query()
                ->where('school_id', $schoolId)
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
        $reportType = (string) $request->input('report_type', 'status');
        $filename = "{$reportType}-report-".now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($schoolId, $reportType) {
            $handle = fopen('php://output', 'w');

            match ($reportType) {
                'department' => $this->writeDepartmentReport($handle, $schoolId),
                'documents' => $this->writeDocumentReport($handle, $schoolId),
                default => $this->writeStatusReport($handle, $schoolId),
            };

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function writeStatusReport($handle, int $schoolId): void
    {
        fputcsv($handle, ['Status Category', 'Total Students']);

        Student::query()
            ->where('school_id', $schoolId)
            ->selectRaw('status_category, COUNT(*) as total')
            ->groupBy('status_category')
            ->orderBy('status_category')
            ->get()
            ->each(fn ($row) => fputcsv($handle, [$row->status_category, $row->total]));
    }

    private function writeDepartmentReport($handle, int $schoolId): void
    {
        fputcsv($handle, ['Department', 'Students', 'Faculty']);

        Department::query()
            ->where('school_id', $schoolId)
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

    private function writeDocumentReport($handle, int $schoolId): void
    {
        fputcsv($handle, ['Student', 'Department', 'Document Count', 'Latest Review Status']);

        Student::query()
            ->where('school_id', $schoolId)
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
}
