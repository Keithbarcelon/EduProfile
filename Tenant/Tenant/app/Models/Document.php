<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use App\Traits\HasDepartmentScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory, BelongsToSchool, HasDepartmentScope;

    protected $fillable = [
        'school_id',
        'student_id',
        'requirement_id',
        'name',
        'file_path',
        'status',
        'reviewed_by',
        'uploaded_at',
        'review_remarks',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    /**
     * Get the student who owns the document.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the requirement this document fulfills.
     */
    public function requirement(): BelongsTo
    {
        return $this->belongsTo(DocumentRequirement::class, 'requirement_id');
    }

    /**
     * Get the user who reviewed the document.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
