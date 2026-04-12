<?php

namespace App\Traits;

use App\Models\School;
use App\Models\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToSchool
{
    /**
     * Boot the trait to apply the global school scope.
     */
    protected static function bootBelongsToSchool(): void
    {
        static::addGlobalScope(new SchoolScope());

        static::creating(function ($model): void {
            if (! $model->school_id) {
                if (app()->bound('request') && request()->attributes->has('tenant')) {
                    $model->school_id = request()->attributes->get('tenant')->id;
                } else {
                    $guard = Auth::guard('web');

                    if ($guard->hasUser()) {
                        $model->school_id = $guard->user()?->school_id;
                    }
                }
            }
        });
    }

    /**
     * Get the school that owns the model.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
