<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Thesis;
use Illuminate\Auth\Access\HandlesAuthorization;

class ThesisPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Thesis');
    }

    public function view(AuthUser $authUser, Thesis $thesis): bool
    {
        return $authUser->can('View:Thesis');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Thesis');
    }

    public function update(AuthUser $authUser, Thesis $thesis): bool
    {
        return $authUser->can('Update:Thesis');
    }

    public function delete(AuthUser $authUser, Thesis $thesis): bool
    {
        return $authUser->can('Delete:Thesis');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Thesis');
    }

    public function restore(AuthUser $authUser, Thesis $thesis): bool
    {
        return $authUser->can('Restore:Thesis');
    }

    public function forceDelete(AuthUser $authUser, Thesis $thesis): bool
    {
        return $authUser->can('ForceDelete:Thesis');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Thesis');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Thesis');
    }

    public function replicate(AuthUser $authUser, Thesis $thesis): bool
    {
        return $authUser->can('Replicate:Thesis');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Thesis');
    }

}