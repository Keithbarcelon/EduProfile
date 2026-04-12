<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'school_type',
        'address',
        'email',
        'contact_number',
        'logo_path',
        'plan_type',
        'plan_started_at',
        'plan_due_at',
        'plan_expiration_email',
        'signup_admin_name',
        'tenant_domain',
        'tenant_database',
        'is_enabled',
        'disabled_at',
        'disable_reason',
        'version',
        'release_notes',
    ];

    protected function casts(): array
    {
        return [
            'plan_started_at' => 'date',
            'plan_due_at' => 'date',
            'is_enabled' => 'boolean',
            'disabled_at' => 'datetime',
        ];
    }

    public function disabledReasonForUsers(): string
    {
        return trim((string) $this->disable_reason) !== ''
            ? (string) $this->disable_reason
            : 'Tenant access was disabled by the administrator.';
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
}
