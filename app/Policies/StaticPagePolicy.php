<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StaticPage;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class StaticPagePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StaticPage');
    }

    public function view(AuthUser $authUser, StaticPage $staticPage): bool
    {
        return $authUser->can('View:StaticPage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StaticPage');
    }

    public function update(AuthUser $authUser, StaticPage $staticPage): bool
    {
        return $authUser->can('Update:StaticPage');
    }

    public function delete(AuthUser $authUser, StaticPage $staticPage): bool
    {
        return $authUser->can('Delete:StaticPage');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:StaticPage');
    }

    public function restore(AuthUser $authUser, StaticPage $staticPage): bool
    {
        return $authUser->can('Restore:StaticPage');
    }

    public function forceDelete(AuthUser $authUser, StaticPage $staticPage): bool
    {
        return $authUser->can('ForceDelete:StaticPage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StaticPage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StaticPage');
    }

    public function replicate(AuthUser $authUser, StaticPage $staticPage): bool
    {
        return $authUser->can('Replicate:StaticPage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StaticPage');
    }
}
