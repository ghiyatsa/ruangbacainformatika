<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\VisitLog;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class VisitLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:VisitLog');
    }

    public function view(AuthUser $authUser, VisitLog $visitLog): bool
    {
        return $authUser->can('View:VisitLog');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:VisitLog');
    }

    public function update(AuthUser $authUser, VisitLog $visitLog): bool
    {
        return $authUser->can('Update:VisitLog');
    }

    public function delete(AuthUser $authUser, VisitLog $visitLog): bool
    {
        return $authUser->can('Delete:VisitLog');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:VisitLog');
    }

    public function restore(AuthUser $authUser, VisitLog $visitLog): bool
    {
        return $authUser->can('Restore:VisitLog');
    }

    public function forceDelete(AuthUser $authUser, VisitLog $visitLog): bool
    {
        return $authUser->can('ForceDelete:VisitLog');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:VisitLog');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:VisitLog');
    }

    public function replicate(AuthUser $authUser, VisitLog $visitLog): bool
    {
        return $authUser->can('Replicate:VisitLog');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:VisitLog');
    }
}
