<?php

namespace App\Policies;

use App\Models\PostCategory;
use App\Models\User;

class PostCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function view(User $user, PostCategory $postCategory): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function create(User $user): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function update(User $user, PostCategory $postCategory): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function delete(User $user, PostCategory $postCategory): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function restore(User $user, PostCategory $postCategory): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function forceDelete(User $user, PostCategory $postCategory): bool
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
