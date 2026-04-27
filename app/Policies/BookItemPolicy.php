<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BookItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:Book');
    }

    public function view(User $user, BookItem $bookItem): bool
    {
        return $user->can('View:Book');
    }

    public function create(User $user): bool
    {
        return $user->can('Update:Book');
    }

    public function update(User $user, BookItem $bookItem): bool
    {
        return $user->can('Update:Book');
    }

    public function delete(User $user, BookItem $bookItem): bool
    {
        return $user->can('Update:Book');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('Update:Book');
    }

    public function restore(User $user, BookItem $bookItem): bool
    {
        return $user->can('Update:Book');
    }

    public function forceDelete(User $user, BookItem $bookItem): bool
    {
        return $user->can('Update:Book');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('Update:Book');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('Update:Book');
    }

    public function replicate(User $user, BookItem $bookItem): bool
    {
        return $user->can('Update:Book');
    }

    public function reorder(User $user): bool
    {
        return $user->can('Update:Book');
    }
}
