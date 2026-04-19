<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'user_id',
        'department_id',
        'student_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'email',
        'phone',
        'course',
        'year_level',
        'section',
        'gender',
        'birthdate',
        'address',
        'guardian_name',
        'guardian_contact',
        'emergency_contact_name',
        'emergency_contact_number',
        'status',
        'status_category',
        'custom_fields',
        'enrolled_at',
    ];

    protected $casts = [
        'school_id'   => 'integer',
        'user_id'     => 'integer',
        'department_id' => 'integer',
        'birthdate'   => 'date',
        'enrolled_at' => 'date',
        'year_level'  => 'integer',
        'custom_fields' => 'array',
    ];

    /**
     * Get the user account associated with the student profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department where the student is enrolled.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the documents uploaded for the student.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get the remarks recorded for the student.
     */
    public function remarks(): HasMany
    {
        return $this->hasMany(Remark::class);
    }

    /**
     * Get the status updates raised for the student.
     */
    public function statusUpdates(): HasMany
    {
        return $this->hasMany(StatusUpdate::class);
    }

    public function customFieldValues(): HasMany
    {
        return $this->hasMany(StudentCustomFieldValue::class);
    }

    /**
     * @return array<string, string>
     */
    public function customFieldValueMap(): array
    {
        $legacy = collect((array) ($this->custom_fields ?? []))
            ->mapWithKeys(fn ($value, $key) => [(string) $key => (string) $value]);

        if ($this->relationLoaded('customFieldValues')) {
            $dynamic = $this->customFieldValues
                ->mapWithKeys(fn (StudentCustomFieldValue $value) => [
                    (string) $value->field_key => (string) ($value->field_value ?? ''),
                ]);

            return $legacy
                ->merge($dynamic)
                ->all();
        }

        return $legacy->all();
    }

    public function getFullNameAttribute(): string
    {
        $name = trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ])));

        return $name !== '' ? $name : $this->first_name . ' ' . $this->last_name;
    }
}
