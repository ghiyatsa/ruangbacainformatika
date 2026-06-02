<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CatalogReport;
use Illuminate\Auth\Access\HandlesAuthorization;

class CatalogReportPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CatalogReport');
    }

    public function view(AuthUser $authUser, CatalogReport $catalogReport): bool
    {
        return $authUser->can('View:CatalogReport');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CatalogReport');
    }

    public function update(AuthUser $authUser, CatalogReport $catalogReport): bool
    {
        return $authUser->can('Update:CatalogReport');
    }

    public function delete(AuthUser $authUser, CatalogReport $catalogReport): bool
    {
        return $authUser->can('Delete:CatalogReport');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CatalogReport');
    }

    public function restore(AuthUser $authUser, CatalogReport $catalogReport): bool
    {
        return $authUser->can('Restore:CatalogReport');
    }

    public function forceDelete(AuthUser $authUser, CatalogReport $catalogReport): bool
    {
        return $authUser->can('ForceDelete:CatalogReport');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CatalogReport');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CatalogReport');
    }

    public function replicate(AuthUser $authUser, CatalogReport $catalogReport): bool
    {
        return $authUser->can('Replicate:CatalogReport');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CatalogReport');
    }

}