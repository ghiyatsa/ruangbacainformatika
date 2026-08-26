<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\WhatsAppMessageLog;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class WhatsAppMessageLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WhatsAppMessageLog');
    }

    public function view(AuthUser $authUser, WhatsAppMessageLog $whatsAppMessageLog): bool
    {
        return $authUser->can('View:WhatsAppMessageLog');
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, WhatsAppMessageLog $whatsAppMessageLog): bool
    {
        return false;
    }

    public function delete(AuthUser $authUser, WhatsAppMessageLog $whatsAppMessageLog): bool
    {
        return false;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function restore(AuthUser $authUser, WhatsAppMessageLog $whatsAppMessageLog): bool
    {
        return false;
    }

    public function forceDelete(AuthUser $authUser, WhatsAppMessageLog $whatsAppMessageLog): bool
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

    public function replicate(AuthUser $authUser, WhatsAppMessageLog $whatsAppMessageLog): bool
    {
        return false;
    }

    public function reorder(AuthUser $authUser): bool
    {
        return false;
    }
}
