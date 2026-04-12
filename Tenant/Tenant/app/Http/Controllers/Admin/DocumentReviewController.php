<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Enums\UserRole;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DocumentReviewController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $this->authorize('viewAny', Document::class);

        $user = Auth::user();
        $schoolId = (int) app('currentSchool')->id;

        $query = Document::query()
            ->where('school_id', $schoolId)
            ->with(['student.department', 'reviewer'])
            ->when(request()->filled('status'), fn ($documentQuery) => $documentQuery->where('status', (string) request()->input('status')));

        if ($user->role === UserRole::ADMISSION->value) {
            $query->whereHas('student', function($q) {
                $q->where('status_category', 'affirmative');
            });
        } elseif (in_array($user->role, [UserRole::DEPARTMENT->value, UserRole::FACULTY->value])) {
            $query->whereHas('student', function($q) use ($user) {
                $q->where('status_category', 'probation')
                    ->where('department_id', $user->department_id ?? 0);
            });
        }

        $documents = $query->latest()->paginate(15);
        $missingStudents = \App\Models\Student::query()
            ->where('school_id', $schoolId)
            ->with('department')
            ->doesntHave('documents')
            ->latest()
            ->limit(10)
            ->get();
        $documentStatusCounts = Document::query()
            ->where('school_id', $schoolId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.documents.index', compact('documents', 'missingStudents', 'documentStatusCounts'));
    }

    public function approve(Document $document): RedirectResponse
    {
        $this->authorize('update', $document);

        $document->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Document approved.');
    }

    public function reject(Document $document, Request $request): RedirectResponse
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'remarks' => 'required|string',
        ]);

        $document->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'review_remarks' => $validated['remarks'],
        ]);

        return back()->with('success', 'Document rejected.');
    }
}
