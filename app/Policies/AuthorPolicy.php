<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Author;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AuthorPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Author');
    }

    public function view(AuthUser $authUser, Author $author): bool
    {
        return $authUser->can('View:Author');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Author');
    }

    public function update(AuthUser $authUser, Author $author): bool
    {
        return $authUser->can('Update:Author');
    }

    public function delete(AuthUser $authUser, Author $author): bool
    {
        return $authUser->can('Delete:Author');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Author');
    }

    public function restore(AuthUser $authUser, Author $author): bool
    {
        return $authUser->can('Restore:Author');
    }

    public function forceDelete(AuthUser $authUser, Author $author): bool
    {
        return $authUser->can('ForceDelete:Author');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Author');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Author');
    }

    public function replicate(AuthUser $authUser, Author $author): bool
    {
        return $authUser->can('Replicate:Author');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Author');
    }
}
