<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Documentation;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class DocumentationPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Documentation');
    }

    public function view(AuthUser $authUser, Documentation $documentation): bool
    {
        return $authUser->can('View:Documentation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Documentation');
    }

    public function update(AuthUser $authUser, Documentation $documentation): bool
    {
        return $authUser->can('Update:Documentation');
    }

    public function delete(AuthUser $authUser, Documentation $documentation): bool
    {
        return $authUser->can('Delete:Documentation');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Documentation');
    }

    public function restore(AuthUser $authUser, Documentation $documentation): bool
    {
        return $authUser->can('Restore:Documentation');
    }

    public function forceDelete(AuthUser $authUser, Documentation $documentation): bool
    {
        return $authUser->can('ForceDelete:Documentation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Documentation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Documentation');
    }

    public function replicate(AuthUser $authUser, Documentation $documentation): bool
    {
        return $authUser->can('Replicate:Documentation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Documentation');
    }
}
