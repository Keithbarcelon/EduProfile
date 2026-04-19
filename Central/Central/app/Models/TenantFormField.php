<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantFormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'module_key',
        'field_key',
        'label',
        'field_type',
        'is_required',
        'options_json',
        'rules_json',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'options_json' => 'array',
        'rules_json' => 'array',
    ];
}
