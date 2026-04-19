<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusUpdate extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'student_id',
        'user_id',
        'old_status',
        'new_status',
        'remarks',
        'approval_status',
        'workflow_key',
        'workflow_step_order',
        'required_role_slug',
        'approval_audit',
        'approved_by',
    ];

    protected $casts = [
        'workflow_step_order' => 'integer',
        'approval_audit' => 'array',
    ];

    /**
     * Get the student associated with the status update.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who initiated the status update.
     */
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who approved the status update.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
