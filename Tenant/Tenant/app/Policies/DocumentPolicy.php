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

        return $user->hasPermission('review_documents');
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

        return $user->hasPermission('review_documents');
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->hasPermission('review_documents');
    }
}
