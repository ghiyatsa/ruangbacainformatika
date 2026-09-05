<?php

namespace App\Policies;

use App\Models\DocumentSubmission;
use App\Models\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentSubmissionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DocumentSubmission') || $authUser->hasAdministrativeRole();
    }

    public function view(AuthUser $authUser, DocumentSubmission $submission): bool
    {
        return $authUser->hasAdministrativeRole() || $authUser->id === $submission->user_id || $authUser->can('View:DocumentSubmission');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->isMahasiswa() || $authUser->hasAdministrativeRole() || $authUser->can('Create:DocumentSubmission');
    }

    public function update(AuthUser $authUser, DocumentSubmission $submission): bool
    {
        if ($authUser->hasAdministrativeRole() || $authUser->can('Update:DocumentSubmission')) {
            return true;
        }

        // Mahasiswa hanya boleh mengupdate dokumen miliknya dan belum berstatus disetujui (Approved)
        return $authUser->id === $submission->user_id && ! $submission->isApproved();
    }

    public function delete(AuthUser $authUser, DocumentSubmission $submission): bool
    {
        return $authUser->can('Delete:DocumentSubmission') || $authUser->hasAdministrativeRole();
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DocumentSubmission') || $authUser->hasAdministrativeRole();
    }
}
