<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ActivityLog;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActivityLogPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ActivityLog');
    }

    public function view(AuthUser $authUser, ActivityLog $activityLog): bool
    {
        return $authUser->can('View:ActivityLog');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ActivityLog');
    }

    public function update(AuthUser $authUser, ActivityLog $activityLog): bool
    {
        return $authUser->can('Update:ActivityLog');
    }

    public function delete(AuthUser $authUser, ActivityLog $activityLog): bool
    {
        return $authUser->can('Delete:ActivityLog');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ActivityLog');
    }

    public function restore(AuthUser $authUser, ActivityLog $activityLog): bool
    {
        return $authUser->can('Restore:ActivityLog');
    }

    public function forceDelete(AuthUser $authUser, ActivityLog $activityLog): bool
    {
        return $authUser->can('ForceDelete:ActivityLog');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ActivityLog');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ActivityLog');
    }

    public function replicate(AuthUser $authUser, ActivityLog $activityLog): bool
    {
        return $authUser->can('Replicate:ActivityLog');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ActivityLog');
    }

}