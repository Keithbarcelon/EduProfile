<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'module_id',
        'is_enabled',
        'config_json',
        'activated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'config_json' => 'array',
            'activated_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
