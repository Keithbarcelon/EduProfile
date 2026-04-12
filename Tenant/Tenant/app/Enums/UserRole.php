<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin'; // For backward compatibility
    case TENANT_ADMIN = 'tenant_admin';
    case ADMISSION = 'admission';
    case DEPARTMENT = 'department';
    case FACULTY = 'faculty';
    case STUDENT = 'student';

    /**
     * Get all role labels.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::ADMIN->value => 'Admin',
            self::TENANT_ADMIN->value => 'Tenant Admin',
            self::ADMISSION->value => 'Admission',
            self::DEPARTMENT->value => 'Department',
            self::FACULTY->value => 'Faculty',
            self::STUDENT->value => 'Student',
        ];
    }

    /**
     * Check if a role is administrative.
     */
    public static function isAdmin(string $role): bool
    {
        return in_array($role, [self::ADMIN->value, self::TENANT_ADMIN->value]);
    }
}
