<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantFeatureFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'flag_key',
        'is_active',
        'meta_json',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'meta_json' => 'array',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
