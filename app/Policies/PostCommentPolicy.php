<?php

namespace App\Policies;

use App\Models\PostComment;
use App\Models\User;

class PostCommentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAdministrativeRole();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PostComment $postComment): bool
    {
        return $user->id === $postComment->user_id || $user->hasAdministrativeRole();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PostComment $postComment): bool
    {
        return $user->id === $postComment->user_id || $user->hasAdministrativeRole();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PostComment $postComment): bool
    {
        return $user->id === $postComment->user_id || $user->hasAdministrativeRole();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PostComment $postComment): bool
    {
        return $user->hasAdministrativeRole();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PostComment $postComment): bool
    {
        return $user->hasAdministrativeRole();
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->hasAdministrativeRole();
    }

    /**
     * Determine whether the user can update any models.
     */
    public function updateAny(User $user): bool
    {
        return $user->hasAdministrativeRole();
    }
}
