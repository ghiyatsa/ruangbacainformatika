<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\KioskDevice;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class KioskDevicePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KioskDevice');
    }

    public function view(AuthUser $authUser, KioskDevice $kioskDevice): bool
    {
        return $authUser->can('View:KioskDevice');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KioskDevice');
    }

    public function update(AuthUser $authUser, KioskDevice $kioskDevice): bool
    {
        return $authUser->can('Update:KioskDevice');
    }

    public function delete(AuthUser $authUser, KioskDevice $kioskDevice): bool
    {
        return $authUser->can('Delete:KioskDevice');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:KioskDevice');
    }

    public function restore(AuthUser $authUser, KioskDevice $kioskDevice): bool
    {
        return $authUser->can('Restore:KioskDevice');
    }

    public function forceDelete(AuthUser $authUser, KioskDevice $kioskDevice): bool
    {
        return $authUser->can('ForceDelete:KioskDevice');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KioskDevice');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KioskDevice');
    }

    public function replicate(AuthUser $authUser, KioskDevice $kioskDevice): bool
    {
        return $authUser->can('Replicate:KioskDevice');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KioskDevice');
    }
}
