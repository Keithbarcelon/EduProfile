<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Remark;
use App\Models\Student;
use App\Enums\UserRole;

class RemarkPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('manage_students');
    }

    public function create(User $user, Student $student): bool
    {
        if (! $user->hasPermission('manage_students')) {
            return false;
        }

        if (UserRole::isAdmin($user->role)) {
            return true;
        }

        if ($user->role === UserRole::FACULTY->value) {
            return $user->department_id !== null && $user->department_id === $student->department_id;
        }

        if ($user->role === UserRole::ADMISSION->value) {
            return $student->status_category === 'affirmative';
        }

        return false;
    }

    public function update(User $user, Remark $remark): bool
    {
        return $user->hasPermission('manage_students');
    }

    public function delete(User $user, Remark $remark): bool
    {
        return $user->hasPermission('manage_students');
    }
}
