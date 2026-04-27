<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Document;
use App\Enums\UserRole;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::STUDENT->value
            || $user->hasPermission('review_documents');
    }

    public function view(User $user, Document $document): bool
    {
        if ($user->role === UserRole::STUDENT->value) {
            return $user->id === $document->student->user_id;
        }

        if (! $user->hasPermission('review_documents')) {
            return false;
        }

        return $this->studentInUsersScope($user, $document);
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::STUDENT->value;
    }

    public function update(User $user, Document $document): bool
    {
        if ($user->role === UserRole::STUDENT->value) {
            return false;
        }

        if (! $user->hasPermission('review_documents')) {
            return false;
        }

        return $this->studentInUsersScope($user, $document);
    }

    public function delete(User $user, Document $document): bool
    {
        if ($user->role === UserRole::STUDENT->value) {
            return $user->id === $document->student->user_id
                && strtolower((string) $document->status) !== 'approved';
        }

        if (! $user->hasPermission('review_documents')) {
            return false;
        }

        return $this->studentInUsersScope($user, $document);
    }

    private function studentInUsersScope(User $user, Document $document): bool
    {
        if (UserRole::isAdmin($user->role) || $user->role === UserRole::ADMISSION->value) {
            return true;
        }

        if (in_array($user->role, [UserRole::DEPARTMENT->value, UserRole::FACULTY->value], true)) {
            return $user->department_id !== null
                && $document->student->department_id === $user->department_id;
        }

        return false;
    }
}
