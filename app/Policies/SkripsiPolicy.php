<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Skripsi;
use Illuminate\Auth\Access\HandlesAuthorization;

class SkripsiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Skripsi');
    }

    public function view(AuthUser $authUser, Skripsi $skripsi): bool
    {
        return $authUser->can('View:Skripsi');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Skripsi');
    }

    public function update(AuthUser $authUser, Skripsi $skripsi): bool
    {
        return $authUser->can('Update:Skripsi');
    }

    public function delete(AuthUser $authUser, Skripsi $skripsi): bool
    {
        return $authUser->can('Delete:Skripsi');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Skripsi');
    }

    public function restore(AuthUser $authUser, Skripsi $skripsi): bool
    {
        return $authUser->can('Restore:Skripsi');
    }

    public function forceDelete(AuthUser $authUser, Skripsi $skripsi): bool
    {
        return $authUser->can('ForceDelete:Skripsi');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Skripsi');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Skripsi');
    }

    public function replicate(AuthUser $authUser, Skripsi $skripsi): bool
    {
        return $authUser->can('Replicate:Skripsi');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Skripsi');
    }

}