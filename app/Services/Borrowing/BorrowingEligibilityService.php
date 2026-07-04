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

        if (! $user->canBorrowBooks()) {
            throw ValidationException::withMessages([
                'draft' => 'Layanan peminjaman tersedia untuk anggota yang sudah memenuhi syarat.',
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
