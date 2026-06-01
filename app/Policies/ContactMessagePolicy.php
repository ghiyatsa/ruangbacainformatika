<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ContactMessage;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactMessagePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ContactMessage');
    }

    public function view(AuthUser $authUser, ContactMessage $contactMessage): bool
    {
        return $authUser->can('View:ContactMessage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ContactMessage');
    }

    public function update(AuthUser $authUser, ContactMessage $contactMessage): bool
    {
        return $authUser->can('Update:ContactMessage');
    }

    public function delete(AuthUser $authUser, ContactMessage $contactMessage): bool
    {
        return $authUser->can('Delete:ContactMessage');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ContactMessage');
    }

    public function restore(AuthUser $authUser, ContactMessage $contactMessage): bool
    {
        return $authUser->can('Restore:ContactMessage');
    }

    public function forceDelete(AuthUser $authUser, ContactMessage $contactMessage): bool
    {
        return $authUser->can('ForceDelete:ContactMessage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ContactMessage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ContactMessage');
    }

    public function replicate(AuthUser $authUser, ContactMessage $contactMessage): bool
    {
        return $authUser->can('Replicate:ContactMessage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ContactMessage');
    }

}