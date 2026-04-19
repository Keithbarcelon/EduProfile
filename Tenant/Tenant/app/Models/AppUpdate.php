<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'title',
        'description',
        'release_date',
        'release_document_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'is_active' => 'boolean',
        ];
    }
}
