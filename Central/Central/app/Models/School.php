<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    protected $fillable = [
        'name',
        'school_type',
        'address',
        'email',
        'contact_number',
        'plan_type',
        'plan_started_at',
        'plan_due_at',
        'billing_cycle',
        'trial_ends_at',
        'plan_expiration_email',
        'signup_admin_name',
        'tenant_domain',
        'requested_tenant_domain',
        'tenant_database',
        'storage_used_mb',
        'bandwidth_used_mb',
        'usage_refreshed_at',
        'is_enabled',
        'disabled_at',
        'disable_reason',
        'version',
        'release_notes',
        'approval_status',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'plan_started_at' => 'date',
            'plan_due_at' => 'date',
            'trial_ends_at' => 'date',
            'storage_used_mb' => 'decimal:2',
            'bandwidth_used_mb' => 'decimal:2',
            'usage_refreshed_at' => 'datetime',
            'is_enabled' => 'boolean',
            'disabled_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function disabledReasonForUsers(): string
    {
        return trim((string) $this->disable_reason) !== ''
            ? (string) $this->disable_reason
            : 'Tenant access was disabled by the administrator.';
    }

    public function isPendingApproval(): bool
    {
        return ($this->approval_status ?? self::STATUS_PENDING) === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return ($this->approval_status ?? self::STATUS_PENDING) === self::STATUS_APPROVED;
    }

    public static function planCatalog(): array
    {
        return [
            'basic' => [
                'label' => 'Basic Plan',
                'student_limit' => 300,
                'user_limit' => 5,
                'features' => [
                    'Status monitoring (Affirmative & Probation)',
                    'Basic reports',
                ],
            ],
            'standard' => [
                'label' => 'Standard Plan',
                'student_limit' => 1500,
                'user_limit' => 20,
                'features' => [
                    'Advanced monitoring dashboard',
                    'Data export (PDF/Excel)',
                ],
            ],
            'premium' => [
                'label' => 'Premium Plan',
                'student_limit' => null,
                'user_limit' => null,
                'features' => [
                    'Advanced analytics dashboard',
                    'Custom branding',
                    'Automated backups',
                ],
            ],
        ];
    }

    public function planSpec(): array
    {
        return self::planCatalog()[$this->plan_type] ?? self::planCatalog()['basic'];
    }

    public function planLimits(): array
    {
        return match ($this->plan_type) {
            'premium' => ['storage_mb' => 10240, 'bandwidth_mb' => 204800],
            'standard' => ['storage_mb' => 5120, 'bandwidth_mb' => 102400],
            default => ['storage_mb' => 2048, 'bandwidth_mb' => 51200],
        };
    }

    public function storageUsagePercent(): float
    {
        $limit = (float) ($this->planLimits()['storage_mb'] ?? 0);

        if ($limit <= 0.0) {
            return 0.0;
        }

        return round((((float) $this->storage_used_mb) / $limit) * 100, 2);
    }

    public function bandwidthUsagePercent(): float
    {
        $limit = (float) ($this->planLimits()['bandwidth_mb'] ?? 0);

        if ($limit <= 0.0) {
            return 0.0;
        }

        return round((((float) $this->bandwidth_used_mb) / $limit) * 100, 2);
    }

    public function isOverUsageLimit(): bool
    {
        return $this->storageUsagePercent() > 100 || $this->bandwidthUsagePercent() > 100;
    }

    public function isSubscriptionExpired(): bool
    {
        return $this->plan_due_at !== null && $this->plan_due_at->isPast();
    }

    public function isSubscriptionExpiringWithinDays(int $days): bool
    {
        if ($this->plan_due_at === null) {
            return false;
        }

        return now()->startOfDay()->diffInDays($this->plan_due_at->startOfDay(), false) <= $days
            && ! $this->isSubscriptionExpired();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function tenantPlans(): HasMany
    {
        return $this->hasMany(TenantPlan::class);
    }
}
