<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function view(User $user, Loan $loan): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Loan $loan): bool
    {
        return false;
    }

    public function delete(User $user, Loan $loan): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasAdministrativeRole();
    }

    public function restore(User $user, Loan $loan): bool
    {
        return false;
    }

    public function forceDelete(User $user, Loan $loan): bool
    {
        return false;
    }
}
