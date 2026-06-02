<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Publisher;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublisherPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Publisher');
    }

    public function view(AuthUser $authUser, Publisher $publisher): bool
    {
        return $authUser->can('View:Publisher');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Publisher');
    }

    public function update(AuthUser $authUser, Publisher $publisher): bool
    {
        return $authUser->can('Update:Publisher');
    }

    public function delete(AuthUser $authUser, Publisher $publisher): bool
    {
        return $authUser->can('Delete:Publisher');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Publisher');
    }

    public function restore(AuthUser $authUser, Publisher $publisher): bool
    {
        return $authUser->can('Restore:Publisher');
    }

    public function forceDelete(AuthUser $authUser, Publisher $publisher): bool
    {
        return $authUser->can('ForceDelete:Publisher');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Publisher');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Publisher');
    }

    public function replicate(AuthUser $authUser, Publisher $publisher): bool
    {
        return $authUser->can('Replicate:Publisher');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Publisher');
    }

}