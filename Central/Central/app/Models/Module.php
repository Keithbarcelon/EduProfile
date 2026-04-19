<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'category',
        'is_core',
        'default_enabled',
        'config_schema_json',
    ];

    protected function casts(): array
    {
        return [
            'is_core' => 'boolean',
            'default_enabled' => 'boolean',
            'config_schema_json' => 'array',
        ];
    }

    public function tenantModules(): HasMany
    {
        return $this->hasMany(TenantModule::class);
    }
}
