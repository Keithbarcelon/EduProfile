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
        if (UserRole::isAdmin($user->role)) {
            return $user->hasPermission('review_documents');
        }

        if ($user->role !== UserRole::STUDENT->value && ! $user->hasPermission('review_documents')) {
            return false;
        }

        if ($user->role === UserRole::STUDENT->value) {
            return $user->id === $document->student->user_id;
        }

        if ($user->role === UserRole::ADMISSION->value) {
            return $document->student->status_category === 'affirmative';
        }

        if (in_array($user->role, [UserRole::DEPARTMENT->value, UserRole::FACULTY->value])) {
            return $user->department_id !== null
                && $document->student->department_id === $user->department_id
                && $document->student->status_category === 'probation';
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::STUDENT->value;
    }

    public function update(User $user, Document $document): bool
    {
        if (UserRole::isAdmin($user->role)) {
            return $user->hasPermission('review_documents');
        }

        if (! $user->hasPermission('review_documents')) {
            return false;
        }

        if ($user->role === UserRole::ADMISSION->value) {
            return $document->student->status_category === 'affirmative';
        }

        if (in_array($user->role, [UserRole::DEPARTMENT->value, UserRole::FACULTY->value])) {
            return $user->department_id !== null
                && $document->student->department_id === $user->department_id
                && $document->student->status_category === 'probation';
        }

        return false;
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->hasPermission('review_documents');
    }
}
