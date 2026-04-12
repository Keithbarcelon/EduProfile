<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\UserRole;

class DashboardPolicy
{
    /**
     * Determine whether the user can view the dashboard.
     */
    public function view(User $user): bool
    {
        return $user->role !== UserRole::STUDENT->value;
    }

    /**
     * Determine whether the user has full dashboard access.
     */
    public function viewFull(User $user): bool
    {
        return UserRole::isAdmin($user->role);
    }
}
