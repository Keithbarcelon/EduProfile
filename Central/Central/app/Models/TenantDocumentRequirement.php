<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantDocumentRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'module_key',
        'status_category',
        'document_name',
        'is_required',
        'rules_json',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'rules_json' => 'array',
    ];
}
