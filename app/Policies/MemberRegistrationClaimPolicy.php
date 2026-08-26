<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MemberRegistrationClaim;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MemberRegistrationClaimPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MemberRegistrationClaim');
    }

    public function view(AuthUser $authUser, MemberRegistrationClaim $memberRegistrationClaim): bool
    {
        return $authUser->can('View:MemberRegistrationClaim');
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, MemberRegistrationClaim $memberRegistrationClaim): bool
    {
        return false;
    }

    public function delete(AuthUser $authUser, MemberRegistrationClaim $memberRegistrationClaim): bool
    {
        return false;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function restore(AuthUser $authUser, MemberRegistrationClaim $memberRegistrationClaim): bool
    {
        return false;
    }

    public function forceDelete(AuthUser $authUser, MemberRegistrationClaim $memberRegistrationClaim): bool
    {
        return false;
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function replicate(AuthUser $authUser, MemberRegistrationClaim $memberRegistrationClaim): bool
    {
        return false;
    }

    public function reorder(AuthUser $authUser): bool
    {
        return false;
    }
}
