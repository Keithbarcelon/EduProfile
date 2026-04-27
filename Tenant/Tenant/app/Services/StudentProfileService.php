<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Student;
use App\Models\StudentCustomFieldValue;
use App\Support\TenantConfig;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

class StudentProfileService
{
    /**
     * @return array{student: Student, message: string}
     */
    public function linkUserToStudent(User $user, int $schoolId): array
    {
        if ($user->role !== UserRole::STUDENT->value) {
            throw new InvalidArgumentException('Only student user accounts can be linked to student profiles.');
        }

        $existingProfile = Student::query()
            ->where('school_id', $schoolId)
            ->where('user_id', $user->id)
            ->first();

        if ($existingProfile) {
            return [
                'student' => $existingProfile,
                'message' => 'Student profile is already linked. You can finalize the details now.',
            ];
        }

        $legacyProfile = Student::query()
            ->where('school_id', $schoolId)
            ->whereNull('user_id')
            ->where('email', $user->email)
            ->first();

        if ($legacyProfile) {
            $legacyProfile->update([
                'user_id' => $user->id,
                'department_id' => $legacyProfile->department_id ?: $user->department_id,
            ]);

            return [
                'student' => $legacyProfile,
                'message' => 'Existing student record linked to the selected user account.',
            ];
        }

        [$firstName, $lastName] = $this->splitName((string) $user->name);

        $student = DB::transaction(function () use ($schoolId, $user, $firstName, $lastName) {
            return Student::create([
                'school_id' => $schoolId,
                'user_id' => $user->id,
                'department_id' => $user->department_id,
                'student_id' => $this->generateUniqueStudentId($schoolId),
                'first_name' => $firstName,
                'middle_name' => null,
                'last_name' => $lastName,
                'suffix' => null,
                'email' => $user->email,
                'phone' => null,
                'course' => 'TBD',
                'year_level' => 1,
                'section' => null,
                'gender' => null,
                'birthdate' => null,
                'address' => null,
                'guardian_name' => null,
                'guardian_contact' => null,
                'emergency_contact_name' => null,
                'emergency_contact_number' => null,
                'status' => 'active',
                'status_category' => 'regular',
                'custom_fields' => [],
                'enrolled_at' => now()->toDateString(),
            ]);
        });

        return [
            'student' => $student,
            'message' => 'Student account linked. Complete the profile details and save changes.',
        ];
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function updateStudentProfile(Student $student, array $validated, int $schoolId): void
    {
        $validated['school_id'] = $schoolId;
        $validated['student_id'] = (string) $student->student_id;
        $normalizedCustomFields = $this->sanitizeCustomFields((array) ($validated['custom_fields'] ?? []));
        $validated['custom_fields'] = $normalizedCustomFields;
        $hasProfileImageColumn = Schema::connection($student->getConnectionName())
            ->hasColumn($student->getTable(), 'profile_image_path');

        DB::transaction(function () use ($student, $validated, $normalizedCustomFields, $schoolId, $hasProfileImageColumn) {
            $newProfileImagePath = $hasProfileImageColumn ? $student->profile_image_path : null;
            $uploadedProfileImage = $validated['profile_image'] ?? null;

            if ($uploadedProfileImage instanceof UploadedFile && ! $hasProfileImageColumn) {
                throw new RuntimeException('Student photo upload is not ready yet. Run tenant migrations to add the profile_image_path column.');
            }

            if ($uploadedProfileImage instanceof UploadedFile) {
                $newProfileImagePath = $uploadedProfileImage->store('student-profiles/' . $student->id, 'public');
            }

            unset($validated['profile_image']);
            if ($hasProfileImageColumn) {
                $validated['profile_image_path'] = $newProfileImagePath;
            }

            $oldProfileImagePath = $hasProfileImageColumn
                ? (string) ($student->profile_image_path ?? '')
                : '';

            $student->update($validated);

            if ($uploadedProfileImage instanceof UploadedFile && $oldProfileImagePath !== '' && $oldProfileImagePath !== $newProfileImagePath) {
                Storage::disk('public')->delete($oldProfileImagePath);
            }

            if (Schema::hasTable('student_custom_field_values')) {
                StudentCustomFieldValue::query()
                    ->where('student_id', $student->id)
                    ->delete();

                foreach ($normalizedCustomFields as $fieldKey => $fieldValue) {
                    StudentCustomFieldValue::query()->create([
                        'school_id' => $schoolId,
                        'student_id' => $student->id,
                        'field_key' => (string) $fieldKey,
                        'field_value' => (string) $fieldValue,
                    ]);
                }
            }

            if (! $student->user) {
                return;
            }

            $student->user->update([
                'department_id' => $validated['department_id'] ?? null,
                'email' => (string) $validated['email'],
                'name' => trim(sprintf('%s %s', (string) $validated['first_name'], (string) $validated['last_name'])),
            ]);
        });
    }

    /**
     * @param array<string, mixed> $submittedFields
     * @return array<string, string>
     */
    private function sanitizeCustomFields(array $submittedFields): array
    {
        $reservedKeys = collect(['student_id'])
            ->map(fn ($fieldKey) => strtolower(trim((string) $fieldKey)))
            ->values();

        $allowedFields = collect(TenantConfig::studentCustomFields())
            ->pluck('field_key')
            ->map(fn ($fieldKey) => (string) $fieldKey)
            ->reject(fn ($fieldKey) => $reservedKeys->contains(strtolower(trim($fieldKey))))
            ->filter()
            ->values();

        if ($allowedFields->isEmpty()) {
            return [];
        }

        $normalized = [];

        foreach ($submittedFields as $fieldKey => $value) {
            $key = (string) $fieldKey;

            if (! $allowedFields->contains($key)) {
                continue;
            }

            if ($value === null) {
                continue;
            }

            $stringValue = trim((string) $value);
            if ($stringValue === '') {
                continue;
            }

            $normalized[$key] = $stringValue;
        }

        return $normalized;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $fullName): array
    {
        $name = trim($fullName);

        if ($name === '') {
            return ['Student', 'Account'];
        }

        $parts = preg_split('/\s+/', $name) ?: [];

        if (count($parts) === 1) {
            return [$parts[0], $parts[0]];
        }

        $first = array_shift($parts) ?? 'Student';
        $last = implode(' ', $parts);

        return [$first, $last === '' ? 'Account' : $last];
    }

    private function generateUniqueStudentId(int $schoolId): string
    {
        do {
            $candidate = 'AUTO-'.now()->format('Y').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Student::query()->where('school_id', $schoolId)->where('student_id', $candidate)->exists());

        return $candidate;
    }

}