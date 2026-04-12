<?php

namespace App\Policies;

use App\Models\User;

class SchoolManagementPolicy
{
    public function manageUsers(User $user): bool
    {
        return $user->hasPermission('manage_users');
    }

    public function manageDepartments(User $user): bool
    {
        return $user->hasPermission('manage_departments');
    }

    public function manageSettings(User $user): bool
    {
        return $user->hasPermission('manage_settings');
    }
}
