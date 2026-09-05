<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\InternshipReportSubmission;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class InternshipReportSubmissionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InternshipReportSubmission');
    }

    public function view(AuthUser $authUser, InternshipReportSubmission $internshipReportSubmission): bool
    {
        return $authUser->can('View:InternshipReportSubmission');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InternshipReportSubmission');
    }

    public function update(AuthUser $authUser, InternshipReportSubmission $internshipReportSubmission): bool
    {
        return $authUser->can('Update:InternshipReportSubmission');
    }

    public function delete(AuthUser $authUser, InternshipReportSubmission $internshipReportSubmission): bool
    {
        return $authUser->can('Delete:InternshipReportSubmission');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:InternshipReportSubmission');
    }

    public function restore(AuthUser $authUser, InternshipReportSubmission $internshipReportSubmission): bool
    {
        return $authUser->can('Restore:InternshipReportSubmission');
    }

    public function forceDelete(AuthUser $authUser, InternshipReportSubmission $internshipReportSubmission): bool
    {
        return $authUser->can('ForceDelete:InternshipReportSubmission');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InternshipReportSubmission');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InternshipReportSubmission');
    }

    public function replicate(AuthUser $authUser, InternshipReportSubmission $internshipReportSubmission): bool
    {
        return $authUser->can('Replicate:InternshipReportSubmission');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InternshipReportSubmission');
    }
}
