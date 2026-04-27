<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Loan;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LoanPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Loan');
    }

    public function view(AuthUser $authUser, Loan $loan): bool
    {
        return $authUser->can('View:Loan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Loan');
    }

    public function update(AuthUser $authUser, Loan $loan): bool
    {
        return $authUser->can('Update:Loan');
    }

    public function delete(AuthUser $authUser, Loan $loan): bool
    {
        return $authUser->can('Delete:Loan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Loan');
    }

    public function restore(AuthUser $authUser, Loan $loan): bool
    {
        return $authUser->can('Restore:Loan');
    }

    public function forceDelete(AuthUser $authUser, Loan $loan): bool
    {
        return $authUser->can('ForceDelete:Loan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Loan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Loan');
    }

    public function replicate(AuthUser $authUser, Loan $loan): bool
    {
        return $authUser->can('Replicate:Loan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Loan');
    }
}
