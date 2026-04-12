<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

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

        DB::transaction(function () use ($student, $validated) {
            $student->update($validated);

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