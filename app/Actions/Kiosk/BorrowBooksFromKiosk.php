<?php

namespace App\Actions\Kiosk;

use App\Models\Loan;
use App\Services\KioskBorrowVerificationService;
use App\Services\KioskLoanService;
use Illuminate\Validation\ValidationException;

class BorrowBooksFromKiosk
{
    public function __construct(
        protected KioskBorrowVerificationService $kioskBorrowVerificationService,
        protected KioskLoanService $kioskLoanService,
    ) {}

    /**
     * @param  array<int, int>  $bookIds
     */
    public function execute(string $verificationPayload, string $memberIdentifier, array $bookIds): Loan
    {
        $member = $this->kioskBorrowVerificationService->resolveUser($verificationPayload);
        $submittedMember = $this->kioskLoanService->findMemberByIdentifier($memberIdentifier);

        if (! $submittedMember || $submittedMember->isNot($member)) {
            throw ValidationException::withMessages([
                'member_identifier' => 'Identitas anggota tidak sesuai dengan member key yang discan.',
            ]);
        }

        $this->kioskBorrowVerificationService->consume($verificationPayload);

        return $this->kioskLoanService->borrow($member->email, $bookIds);
    }
}
