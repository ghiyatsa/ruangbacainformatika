<?php

namespace App\Actions\Loans;

use App\Models\Loan;
use App\Services\LoanDraftService;

class ConsumeLoanDraft
{
    public function __construct(
        protected LoanDraftService $loanDraftService,
    ) {}

    public function execute(string $payload): Loan
    {
        return $this->loanDraftService->consume($payload);
    }
}
