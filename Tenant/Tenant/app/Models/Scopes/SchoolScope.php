<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class SchoolScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Prefer the resolved tenant so auth can safely hydrate the session user.
        if (app()->bound('request') && request()->attributes->has('tenant')) {
            $tenant = request()->attributes->get('tenant');

            if ($tenant && isset($tenant->id)) {
                $builder->where($model->getTable() . '.school_id', $tenant->id);
                return;
            }
        }

        // Only use the authenticated user if the guard already has a loaded instance.
        $guard = Auth::guard('web');

        if ($guard->hasUser() && ($schoolId = $guard->user()?->school_id)) {
            $builder->where($model->getTable() . '.school_id', $schoolId);
        }
    }
}
