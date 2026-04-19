<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Support\TenantConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && in_array($user->role, ['admin', 'tenant_admin', 'admission']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $studentId = $this->route('student')?->id;
        $schoolId = (int) app('currentSchool')->id;
        $statusCategory = strtolower((string) ($this->input('status_category') ?: $this->route('student')?->status_category ?: 'regular'));

        $rules = [
            'student_id' => ['required', 'string', 'max:20', Rule::unique('students', 'student_id')->ignore($studentId)],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('school_id', $schoolId)
                    ->where('role', UserRole::STUDENT->value)),
                Rule::unique('students', 'user_id')->ignore($studentId),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', Rule::unique('students', 'email')->ignore($studentId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'course' => ['required', 'string', 'max:50'],
            'department_id' => [
                'nullable',
                'integer',
                Rule::exists('departments', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'year_level' => ['required', 'integer', 'min:1', 'max:6'],
            'section' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'in:Male,Female,Other'],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:500'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_contact' => ['nullable', 'string', 'max:20'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_number' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive,graduated,dropped'],
            'status_category' => ['nullable', 'in:affirmative,probation,regular'],
            'enrolled_at' => ['nullable', 'date'],
            'custom_fields' => ['nullable', 'array'],
        ];

        foreach (TenantConfig::studentCustomFields() as $field) {
            $fieldKey = (string) ($field['field_key'] ?? '');

            if ($fieldKey === '') {
                continue;
            }

            $type = (string) ($field['field_type'] ?? 'text');
            $isRequired = (bool) ($field['is_required'] ?? false);
            $visibleStatuses = collect((array) ($field['visible_statuses'] ?? []))
                ->map(fn ($value) => strtolower(trim((string) $value)))
                ->filter()
                ->values()
                ->all();
            $options = collect((array) ($field['options'] ?? []))
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->values()
                ->all();

            $isVisibleForStatus = $visibleStatuses === [] || in_array($statusCategory, $visibleStatuses, true);

            $fieldRules = [$isVisibleForStatus && $isRequired ? 'required' : 'nullable'];

            if ($type === 'number') {
                $fieldRules[] = 'numeric';
            } elseif ($type === 'date') {
                $fieldRules[] = 'date';
            } else {
                $fieldRules[] = 'string';
                $fieldRules[] = 'max:1000';
            }

            if ($type === 'select' && $options !== []) {
                $fieldRules[] = Rule::in($options);
            }

            $rules['custom_fields.' . $fieldKey] = $fieldRules;
        }

        return $rules;
    }
}
