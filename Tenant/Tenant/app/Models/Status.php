<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Get all students currently tagged with this status.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'current_status_id');
    }
}
