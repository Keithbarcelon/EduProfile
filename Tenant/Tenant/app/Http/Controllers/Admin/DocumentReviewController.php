<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use App\Enums\UserRole;
use App\Support\TenantConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentReviewController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $this->authorize('viewAny', Document::class);

        $user = Auth::user();
        $schoolId = (int) app('currentSchool')->id;
        $search = trim((string) request()->input('q', ''));
        $status = trim((string) request()->input('status', ''));
        $departmentId = (int) request()->input('department_id', 0);
        $reviewerId = (int) request()->input('reviewer_id', 0);
        $uploadedFrom = trim((string) request()->input('uploaded_from', ''));
        $uploadedTo = trim((string) request()->input('uploaded_to', ''));

        $baseQuery = Document::query()
            ->where('school_id', $schoolId)
            ->with(['student.department', 'reviewer'])
            ->when($departmentId > 0, function ($documentQuery) use ($departmentId) {
                $documentQuery->whereHas('student', fn ($studentQuery) => $studentQuery->where('department_id', $departmentId));
            })
            ->when($reviewerId > 0, fn ($documentQuery) => $documentQuery->where('reviewed_by', $reviewerId))
            ->when($uploadedFrom !== '', fn ($documentQuery) => $documentQuery->whereDate('created_at', '>=', $uploadedFrom))
            ->when($uploadedTo !== '', fn ($documentQuery) => $documentQuery->whereDate('created_at', '<=', $uploadedTo))
            ->when($search !== '', function ($documentQuery) use ($search) {
                $documentQuery->where(function ($innerQuery) use ($search): void {
                    $innerQuery->where('name', 'like', "%{$search}%")
                        ->orWhereHas('student', function ($studentQuery) use ($search): void {
                            $studentQuery->where('student_id', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            });

        $this->applyRoleVisibilityConstraints($baseQuery, $user);

        $documentsQuery = (clone $baseQuery)
            ->when($status !== '', fn ($documentQuery) => $documentQuery->where('status', $status));

        $documents = $documentsQuery->latest()->paginate(15)->withQueryString();

        $departments = Department::query()
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $reviewers = User::query()
            ->where('school_id', $schoolId)
            ->whereIn('role', [
                UserRole::ADMIN->value,
                UserRole::TENANT_ADMIN->value,
                UserRole::ADMISSION->value,
                UserRole::DEPARTMENT->value,
                UserRole::FACULTY->value,
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        $students = Student::query()
            ->where('school_id', $schoolId)
            ->when($user->role === UserRole::ADMISSION->value, fn ($studentQuery) => $studentQuery->where('status_category', 'affirmative'))
            ->when(in_array($user->role, [UserRole::DEPARTMENT->value, UserRole::FACULTY->value], true), function ($studentQuery) use ($user): void {
                if ($user->department_id === null) {
                    $studentQuery->whereRaw('1 = 0');

                    return;
                }

                $studentQuery->where('department_id', $user->department_id);
            })
            ->with(['department', 'documents:id,student_id,name'])
            ->latest()
            ->get();

        $missingDocumentMap = [];
        $missingStudents = $students
            ->filter(function (Student $student) use (&$missingDocumentMap): bool {
                $status = strtolower((string) ($student->status_category ?: 'regular'));
                $required = collect(TenantConfig::requiredDocumentNamesForStatus($status))
                    ->map(fn ($name) => trim((string) $name))
                    ->filter()
                    ->unique()
                    ->values();

                if ($required->isEmpty()) {
                    return false;
                }

                $submittedNormalized = $student->documents
                    ->pluck('name')
                    ->map(fn ($name) => $this->normalizeDocumentName((string) $name))
                    ->filter()
                    ->unique();

                $missing = $required
                    ->reject(fn (string $name) => $submittedNormalized->contains($this->normalizeDocumentName($name)))
                    ->values();

                if ($missing->isEmpty()) {
                    return false;
                }

                $missingDocumentMap[(int) $student->id] = $missing->all();

                return true;
            })
            ->take(10)
            ->values();

        $documentStatusCounts = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.documents.index', compact(
            'documents',
            'missingStudents',
            'missingDocumentMap',
            'documentStatusCounts',
            'departments',
            'reviewers'
        ));
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

    public function view(Document $document): BinaryFileResponse|Response
    {
        $this->authorize('view', $document);

        $path = trim((string) $document->file_path);

        if ($path === '' || ! Storage::disk('public')->exists($path)) {
            abort(404, 'Document file not found.');
        }

        return response()->file(Storage::disk('public')->path($path));
    }

    private function normalizeDocumentName(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name) ?? ''));
    }

    private function applyRoleVisibilityConstraints(Builder $query, User $user): void
    {
        if (UserRole::isAdmin($user->role)) {
            return;
        }

        if ($user->role === UserRole::ADMISSION->value) {
            $query->whereHas('student', function (Builder $studentQuery): void {
                $studentQuery->where('status_category', 'affirmative');
            });

            return;
        }

        if (in_array($user->role, [UserRole::DEPARTMENT->value, UserRole::FACULTY->value], true)) {
            $query->whereHas('student', function (Builder $studentQuery) use ($user): void {
                if ($user->department_id === null) {
                    $studentQuery->whereRaw('1 = 0');

                    return;
                }

                $studentQuery->where('department_id', $user->department_id);
            });

            return;
        }

        if (! $user->hasPermission('review_documents')) {
            $query->whereRaw('1 = 0');
        }
    }
}
