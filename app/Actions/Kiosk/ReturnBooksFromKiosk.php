<?php

namespace App\Actions\Kiosk;

use App\Services\KioskBorrowVerificationService;
use App\Services\KioskLoanService;
use Illuminate\Validation\ValidationException;

class ReturnBooksFromKiosk
{
    public function __construct(
        protected KioskBorrowVerificationService $kioskBorrowVerificationService,
        protected KioskLoanService $kioskLoanService,
    ) {}

    /**
     * @param  array<int, int>  $bookIds
     */
    public function execute(string $verificationPayload, string $memberIdentifier, array $bookIds): int
    {
        $member = $this->kioskBorrowVerificationService->resolveUser($verificationPayload);
        $submittedMember = $this->kioskLoanService->findMemberByIdentifier($memberIdentifier);

        if (! $submittedMember || $submittedMember->isNot($member)) {
            throw ValidationException::withMessages([
                'member_identifier' => 'Identitas anggota tidak sesuai dengan member key yang discan.',
            ]);
        }

        $returnedCount = $this->kioskLoanService->returnBooksByBookIds($member->email, $bookIds);

        $this->kioskBorrowVerificationService->consume($verificationPayload);

        return $returnedCount;
    }
}
