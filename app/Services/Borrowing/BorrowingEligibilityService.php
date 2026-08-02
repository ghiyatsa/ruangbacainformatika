<?php

namespace App\Services\Borrowing;

use App\Models\User;
use App\Services\KioskLoanService;
use Illuminate\Validation\ValidationException;

class BorrowingEligibilityService
{
    public function __construct(
        protected KioskLoanService $kioskLoanService,
    ) {}

    public function ensureLoanDraftAccess(User $user): void
    {
        $user->syncMemberRoleState();

        $reason = $user->borrowingBlockReason();

        if ($reason !== null) {
            throw ValidationException::withMessages([
                'draft' => $reason['message'],
            ]);
        }
    }

    public function ensureBorrowingProfileIsReady(User $user): void
    {
        if (! $user->hasRequiredProfileDetails()) {
            throw ValidationException::withMessages([
                'draft' => 'Nomor WhatsApp dan alamat wajib diisi pada profil sebelum meminjam buku.',
            ]);
        }

        $restrictionMessage = $this->kioskLoanService->borrowingRestrictionMessage($user);

        if ($restrictionMessage !== null) {
            throw ValidationException::withMessages([
                'draft' => $restrictionMessage,
            ]);
        }
    }
}
