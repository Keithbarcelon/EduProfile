<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Support\TenantConfig;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $schoolId = (int) app('currentSchool')->id;

        $student = Student::query()
            ->where('school_id', $schoolId)
            ->whereKey(optional($user->resolveStudentProfile())->id)
            ->with([
                'department',
                'documents' => fn ($query) => $query->latest(),
                'remarks' => fn ($query) => $query->with('user')->latest(),
                'statusUpdates' => fn ($query) => $query->with(['initiator', 'approver'])->latest(),
            ])
            ->first();

        abort_if(! $student, 403, 'No student profile found for this account.');

        $documents = $student->documents;
        $remarks = $student->remarks;
        $statusUpdates = $student->statusUpdates;
        $currentStatus = strtolower((string) ($student->status_category ?? 'regular'));

        $statusConfig = [
            'regular' => [
                'label' => TenantConfig::setting('status.label.regular', 'Regular') ?: 'Regular',
                'badge' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                'panel' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
                'message' => TenantConfig::setting('status.message.regular', 'You are currently under Regular status. Continue maintaining your academic requirements.')
                    ?: 'You are currently under Regular status. Continue maintaining your academic requirements.',
            ],
            'affirmative' => [
                'label' => TenantConfig::setting('status.label.affirmative', 'Affirmative') ?: 'Affirmative',
                'badge' => 'bg-amber-100 text-amber-700 ring-amber-200',
                'panel' => 'border-amber-200 bg-amber-50 text-amber-900',
                'message' => TenantConfig::setting('status.message.affirmative', 'You are currently under Affirmative status. Please submit the required documents.')
                    ?: 'You are currently under Affirmative status. Please submit the required documents.',
            ],
            'probation' => [
                'label' => TenantConfig::setting('status.label.probation', 'Probation') ?: 'Probation',
                'badge' => 'bg-rose-100 text-rose-700 ring-rose-200',
                'panel' => 'border-rose-200 bg-rose-50 text-rose-900',
                'message' => TenantConfig::setting('status.message.probation', 'You are currently under Probation status. Review your remarks and complete all required interventions.')
                    ?: 'You are currently under Probation status. Review your remarks and complete all required interventions.',
            ],
        ];

        $statusMeta = $statusConfig[$currentStatus] ?? $statusConfig['regular'];

        $documentCounts = [
            'total' => $documents->count(),
            'pending' => $documents->where('status', 'pending')->count(),
            'approved' => $documents->where('status', 'approved')->count(),
            'rejected' => $documents->where('status', 'rejected')->count(),
        ];

        $requiredDocumentNames = collect(TenantConfig::requiredDocumentNamesForStatus($currentStatus));
        if ($requiredDocumentNames->isEmpty()) {
            $requiredDocumentNames = in_array($currentStatus, ['affirmative', 'probation'], true)
                ? collect([
                    'Letter of Explanation',
                    'Parent Consent',
                    'Commitment Form',
                    'Other required documents',
                ])
                : collect();
        }

        $requiredDocumentNames = $requiredDocumentNames
            ->map(fn ($name) => $this->studentVisibleDocumentName((string) $name, $currentStatus))
            ->filter()
            ->unique()
            ->values();

        $submittedDocumentNames = $documents
            ->pluck('name')
            ->map(fn ($name) => $this->studentVisibleDocumentName(trim((string) $name), $currentStatus))
            ->filter()
            ->unique();

        $documentDisplayNames = $documents
            ->mapWithKeys(fn ($document) => [
                (int) $document->id => $this->studentVisibleDocumentName((string) ($document->name ?? ''), $currentStatus),
            ])
            ->all();

        $pendingRequiredDocuments = $requiredDocumentNames
            ->reject(fn ($name) => $submittedDocumentNames->contains($name))
            ->values();

        $notifications = collect();

        if ($pendingRequiredDocuments->isNotEmpty()) {
            $notifications->push('You have pending documents to submit.');
        }

        if ($documentCounts['rejected'] > 0) {
            $notifications->push('One or more uploaded documents were rejected. Review the remarks below.');
        }

        if (in_array($currentStatus, ['affirmative', 'probation'], true) && $remarks->isNotEmpty()) {
            $notifications->push('You have intervention notes that need your attention.');
        }

        return view('student.dashboard', compact(
            'student',
            'documents',
            'remarks',
            'statusUpdates',
            'statusMeta',
            'documentCounts',
            'requiredDocumentNames',
            'documentDisplayNames',
            'pendingRequiredDocuments',
            'notifications'
        ));
    }

    private function studentVisibleDocumentName(string $name, string $statusCategory): string
    {
        $normalizedName = trim($name);
        $normalizedStatus = strtolower(trim($statusCategory));

        if ($normalizedName === '') {
            return '';
        }

        if ($normalizedStatus !== 'affirmative') {
            return $normalizedName;
        }

        $lower = strtolower($normalizedName);

        if (str_contains($lower, 'gwa')) {
            return 'GWA Record (Latest)';
        }

        $sensitiveTokens = ['ip', 'solo parent', 'indigent', 'pwd'];
        foreach ($sensitiveTokens as $token) {
            if (str_contains($lower, $token)) {
                return 'Affirmative Eligibility Certification';
            }
        }

        return $normalizedName;
    }
}
