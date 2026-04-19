<?php

namespace App\Policies;

use App\Models\User;
use App\Models\StatusUpdate;
use App\Enums\UserRole;

class StatusUpdatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('manage_status_updates');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage_status_updates');
    }

    public function approve(User $user, StatusUpdate $statusUpdate): bool
    {
        if (! $user->hasPermission('manage_status_updates')) {
            return false;
        }

        if (UserRole::isAdmin($user->role)) {
            return true;
        }

        $requiredRole = strtolower((string) ($statusUpdate->required_role_slug ?? ''));
        if ($requiredRole !== '') {
            $userRole = strtolower((string) $user->role);
            if ($userRole === 'admin') {
                $userRole = 'tenant_admin';
            }

            return $userRole === $requiredRole;
        }

        if ($user->role === UserRole::DEPARTMENT->value) {
            return $user->department_id !== null
                && $statusUpdate->student->department_id === $user->department_id;
        }

        return false;
    }

    public function update(User $user, StatusUpdate $statusUpdate): bool
    {
        return $user->hasPermission('manage_status_updates');
    }

    public function delete(User $user, StatusUpdate $statusUpdate): bool
    {
        return $user->hasPermission('manage_status_updates');
    }
}
