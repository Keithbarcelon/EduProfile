<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'student_status_history';

    public const UPDATED_AT = null;

    protected $fillable = [
        'student_id',
        'old_status',
        'new_status',
        'changed_by',
        'role',
        'reason',
    ];

    /**
     * Get the student tied to this status history record.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who changed the student status.
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
