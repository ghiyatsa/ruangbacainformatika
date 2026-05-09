<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\InternshipReport;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class InternshipReportPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InternshipReport');
    }

    public function view(AuthUser $authUser, InternshipReport $internshipReport): bool
    {
        return $authUser->can('View:InternshipReport');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InternshipReport');
    }

    public function update(AuthUser $authUser, InternshipReport $internshipReport): bool
    {
        return $authUser->can('Update:InternshipReport');
    }

    public function delete(AuthUser $authUser, InternshipReport $internshipReport): bool
    {
        return $authUser->can('Delete:InternshipReport');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:InternshipReport');
    }

    public function restore(AuthUser $authUser, InternshipReport $internshipReport): bool
    {
        return $authUser->can('Restore:InternshipReport');
    }

    public function forceDelete(AuthUser $authUser, InternshipReport $internshipReport): bool
    {
        return $authUser->can('ForceDelete:InternshipReport');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InternshipReport');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InternshipReport');
    }

    public function replicate(AuthUser $authUser, InternshipReport $internshipReport): bool
    {
        return $authUser->can('Replicate:InternshipReport');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InternshipReport');
    }
}
