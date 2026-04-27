<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Student;
use App\Enums\UserRole;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('manage_students');
    }

    public function view(User $user, Student $student): bool
    {
        if (! $user->hasPermission('manage_students')) {
            return false;
        }

        if (UserRole::isAdmin($user->role) || $user->role === UserRole::ADMISSION->value) {
            return true;
        }

        if (in_array($user->role, [UserRole::DEPARTMENT->value, UserRole::FACULTY->value])) {
            return $user->department_id !== null
                && $user->department_id === $student->department_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage_students');
    }

    public function update(User $user, Student $student): bool
    {
        return $this->view($user, $student);
    }

    public function delete(User $user, Student $student): bool
    {
        return $this->view($user, $student);
    }
}
