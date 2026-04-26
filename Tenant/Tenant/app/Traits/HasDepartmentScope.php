<?php

namespace App\Traits;

use App\Enums\UserRole;
use App\Models\Document;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasDepartmentScope
{
    /**
     * Scope a query to only include students or users within the authorized department.
     */
    public function scopeByDepartment(Builder $query): Builder
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user) {
            return $query;
        }

        // Special handling for Documents since they link to students but don't have department_id themselves.
        if ($this instanceof Document) {
            // Check for explicit review permission which grants broad access.
            if ($user->hasPermission('review_documents')) {
                return $query;
            }

            return $query->whereHas('student', function (Builder $studentQuery) {
                $studentQuery->byDepartment();
            });
        }

        // Admin and Admission roles see all departments within the tenant.
        if (in_array($user->role, [UserRole::ADMIN->value, UserRole::TENANT_ADMIN->value, UserRole::ADMISSION->value])) {
            return $query;
        }

        // Faculty and Department roles are restricted to their assigned department.
        if (in_array($user->role, [UserRole::DEPARTMENT->value, UserRole::FACULTY->value])) {
            $departmentId = $user->department_id;

            return $query->where(function (Builder $builder) use ($departmentId) {
                if ($departmentId !== null) {
                    $builder->where('department_id', $departmentId)
                        ->orWhereNull('department_id');
                } else {
                    $builder->whereNull('department_id');
                }
            });
        }

        // Students can only see their own data - this is usually handled by other policies/scopes 
        // but we ensure department matching if applicable.
        if ($user->role === UserRole::STUDENT->value) {
            return $query->where('id', $user->id);
        }

        return $query;
    }
}
