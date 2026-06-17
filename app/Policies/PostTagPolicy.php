<?php

namespace App\Policies;

use App\Models\PostTag;
use App\Models\User;

class PostTagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function view(User $user, PostTag $postTag): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function create(User $user): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function update(User $user, PostTag $postTag): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function delete(User $user, PostTag $postTag): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function restore(User $user, PostTag $postTag): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function forceDelete(User $user, PostTag $postTag): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasAdministrativeRole();
    }
}
