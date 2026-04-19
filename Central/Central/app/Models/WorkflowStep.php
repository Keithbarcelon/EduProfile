<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_template_id',
        'step_order',
        'step_name',
        'role_slug',
        'rules_json',
        'sla_hours',
        'is_active',
    ];

    protected $casts = [
        'rules_json' => 'array',
        'is_active' => 'boolean',
    ];
}
