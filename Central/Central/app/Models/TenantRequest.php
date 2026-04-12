<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'tenant_name',
        'address',
        'plan_type',
        'plan_started_at',
        'plan_due_at',
        'signup_admin_name',
        'admin_email',
        'admin_password',
        'plan_expiration_email',
        'requested_tenant_domain',
        'status',
        'reviewed_by_user_id',
        'reviewed_at',
        'rejection_reason',
        'approved_school_id',
        'submitted_ip',
        'submitted_user_agent',
    ];

    protected $hidden = [
        'admin_password',
    ];

    protected function casts(): array
    {
        return [
            'plan_started_at' => 'date',
            'plan_due_at' => 'date',
            'reviewed_at' => 'datetime',
            'admin_password' => 'encrypted',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function approvedSchool(): BelongsTo
    {
        return $this->belongsTo(School::class, 'approved_school_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
