<?php

namespace App\Actions\Loans;

use App\Models\User;
use App\Services\LoanDraftService;

class GenerateLoanDraftQr
{
    public function __construct(
        protected LoanDraftService $loanDraftService,
    ) {}

    /**
     * @param  array<int, int>  $selectedBookIds
     * @return array<string, mixed>
     */
    public function execute(User $user, array $selectedBookIds): array
    {
        return $this->loanDraftService->generateQr($user, $selectedBookIds);
    }
}
