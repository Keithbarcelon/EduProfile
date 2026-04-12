<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $schoolId = (int) app('currentSchool')->id;

        $students = Student::query()->where('school_id', $schoolId);

        $totalStudents = (clone $students)->count();
        $newThisMonth = (clone $students)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $pendingDocuments = Document::query()
            ->where('school_id', $schoolId)
            ->where('status', 'pending')
            ->count();
        $totalUsers = User::query()
            ->where('school_id', $schoolId)
            ->count();
        $totalDepartments = Department::query()
            ->where('school_id', $schoolId)
            ->count();
        $missingSubmissions = Student::query()
            ->where('school_id', $schoolId)
            ->doesntHave('documents')
            ->count();

        $courseBreakdown = Student::query()
            ->where('school_id', $schoolId)
            ->selectRaw('course, COUNT(*) as total')
            ->groupBy('course')
            ->orderByDesc('total')
            ->get();

        $statusCounts = Student::query()
            ->where('school_id', $schoolId)
            ->selectRaw('status_category, COUNT(*) as total')
            ->groupBy('status_category')
            ->pluck('total', 'status_category');

        $documentStatusCounts = Document::query()
            ->where('school_id', $schoolId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $recentStudents = Student::query()
            ->where('school_id', $schoolId)
            ->with('department')
            ->latest()
            ->limit(8)
            ->get();

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
            'recentStudents'
        ));
    }
}
