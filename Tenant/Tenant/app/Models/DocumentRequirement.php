<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentRequirement extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'required_for_status',
        'created_by',
    ];

    /**
     * Get the user who created the requirement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the documents uploaded for this requirement.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'requirement_id');
    }
}
