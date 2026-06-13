<?php

namespace App\Actions\Loans;

use App\Models\LoanDraft;
use App\Models\User;
use App\Services\LoanDraftService;

class AddBookToLoanDraft
{
    public function __construct(
        protected LoanDraftService $loanDraftService,
    ) {}

    public function execute(User $user, int $bookId): LoanDraft
    {
        return $this->loanDraftService->addBook($user, $bookId);
    }
}
