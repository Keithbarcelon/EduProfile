<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_reports');
    }

    public function viewFull(User $user): bool
    {
        return $user->hasPermission('view_reports');
    }

    public function viewLimited(User $user): bool
    {
        return $user->hasPermission('view_reports');
    }
}
