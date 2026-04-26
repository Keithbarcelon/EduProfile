<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentDocumentRequirement;
use App\Models\StudentStatusHistory;
use App\Support\StudentStatusRules;
use App\Support\TenantConfig;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentStatusController extends Controller
{
    use AuthorizesRequests;

    public function setStatus(Request $request, Student $student): JsonResponse
    {
        $this->authorize('view', $student);

        if (! Schema::hasTable('statuses') || ! Schema::hasTable('student_status_history')) {
            return response()->json([
                'message' => 'Status management tables are not yet available for this tenant. Please run tenant migrations.',
            ], 503);
        }

        $validated = $request->validate([
            'status_id' => ['required', 'integer', 'exists:statuses,id'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $user = $request->user();

        /** @var Status $newStatus */
        $newStatus = Status::query()->findOrFail((int) $validated['status_id']);
        $newStatusName = strtolower((string) $newStatus->name);

        if (! StudentStatusRules::canRoleAssignStatus((string) $user->role, $newStatusName)) {
            return response()->json([
                'message' => 'Your role is not allowed to assign this status.',
            ], 403);
        }

        $oldStatus = $student->currentStatus;
        $oldStatusName = strtolower((string) ($oldStatus?->name ?: $student->status_category ?: 'regular'));

        if ($oldStatusName === $newStatusName) {
            return response()->json([
                'message' => 'Student is already assigned to this status.',
            ], 422);
        }

        $allowSkip = StudentStatusRules::canSkipWorkflow((string) $user->role);
        if (! StudentStatusRules::isTransitionAllowed($oldStatusName, $newStatusName, $allowSkip)) {
            return response()->json([
                'message' => 'Status transition is not allowed for this role. Please follow the approved workflow path.',
            ], 422);
        }

        DB::transaction(function () use ($student, $newStatus, $newStatusName, $oldStatusName, $validated, $user): void {
            StudentStatusHistory::query()->create([
                'student_id' => $student->id,
                'old_status' => $oldStatusName,
                'new_status' => $newStatusName,
                'changed_by' => (int) $user->id,
                'role' => StudentStatusRules::normalizeRole((string) $user->role),
                'reason' => trim((string) $validated['reason']),
            ]);

            $student->update([
                'current_status_id' => $newStatus->id,
                'status_category' => $newStatusName,
            ]);

            $this->syncDocumentRequirements($student->id, $newStatusName);
        });

        $student->refresh();

        return response()->json([
            'message' => 'Student status updated successfully.',
            'data' => [
                'student_id' => $student->id,
                'current_status_id' => (int) $student->current_status_id,
                'current_status_name' => strtolower((string) ($student->currentStatus?->name ?: $student->status_category)),
            ],
        ]);
    }

    private function syncDocumentRequirements(int $studentId, string $statusName): void
    {
        $requiredNames = collect(TenantConfig::requiredDocumentNamesForStatus($statusName))
            ->map(fn ($name) => $this->normalizeDocumentName((string) $name))
            ->filter()
            ->unique()
            ->values();

        $existing = StudentDocumentRequirement::query()
            ->where('student_id', $studentId)
            ->get();

        foreach ($requiredNames as $documentName) {
            StudentDocumentRequirement::query()->updateOrCreate(
                [
                    'student_id' => $studentId,
                    'document_name' => $documentName,
                ],
                [
                    'required_for_status' => $statusName,
                    'state' => 'required',
                ]
            );
        }

        $existing
            ->reject(fn (StudentDocumentRequirement $item) => $requiredNames->contains($this->normalizeDocumentName((string) $item->document_name)))
            ->each(function (StudentDocumentRequirement $item): void {
                if ($item->state === 'required') {
                    $item->update([
                        'state' => 'archived',
                    ]);
                }
            });
    }

    private function normalizeDocumentName(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name) ?? ''));
    }
}
