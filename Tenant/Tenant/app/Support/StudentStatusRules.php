<?php

namespace App\Support;

use App\Enums\UserRole;

class StudentStatusRules
{
    /**
     * @return array<int, string>
     */
    public static function allowedStatusNamesForRole(string $role): array
    {
        $normalizedRole = static::normalizeRole($role);

        if (in_array($normalizedRole, [UserRole::ADMIN->value, UserRole::TENANT_ADMIN->value], true)) {
            return static::allStatusNames();
        }

        return match ($normalizedRole) {
            UserRole::ADMISSION->value => ['affirmative'],
            UserRole::DEPARTMENT->value,
            UserRole::FACULTY->value => ['probation'],
            default => [],
        };
    }

    public static function canRoleAssignStatus(string $role, string $statusName): bool
    {
        return in_array(strtolower(trim($statusName)), static::allowedStatusNamesForRole($role), true);
    }

    public static function canSkipWorkflow(string $role): bool
    {
        return static::isAdminRole($role);
    }

    public static function isTransitionAllowed(string $oldStatus, string $newStatus, bool $allowSkip = false): bool
    {
        $from = strtolower(trim($oldStatus));
        $to = strtolower(trim($newStatus));

        if ($from === '' || $to === '') {
            return false;
        }

        if ($from === $to) {
            return false;
        }

        if ($allowSkip) {
            return true;
        }

        $statusOrder = [
            'regular' => 1,
            'affirmative' => 2,
            'probation' => 3,
        ];

        if (! isset($statusOrder[$from], $statusOrder[$to])) {
            return false;
        }

        return abs($statusOrder[$from] - $statusOrder[$to]) === 1;
    }

    public static function isAdminRole(string $role): bool
    {
        return UserRole::isAdmin(static::normalizeRole($role));
    }

    public static function normalizeRole(string $role): string
    {
        $normalized = strtolower(trim($role));

        return $normalized === UserRole::ADMIN->value
            ? UserRole::TENANT_ADMIN->value
            : $normalized;
    }

    /**
     * @return array<int, string>
     */
    public static function allStatusNames(): array
    {
        return ['regular', 'affirmative', 'probation'];
    }
}
