<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'current_version',
        'last_checked_at',
        'latest_seen_version',
        'acknowledged_at',
    ];

    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'last_checked_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(School::class, 'tenant_id');
    }
}
