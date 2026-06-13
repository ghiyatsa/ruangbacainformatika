<?php

namespace App\Services\Borrowing;

use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\User;
use App\Services\KioskLoanService;
use Illuminate\Database\Eloquent\Builder;

class LoanLimitService
{
    public function __construct(
        protected KioskLoanService $kioskLoanService,
    ) {}

    public function loanMaxBooks(): int
    {
        return $this->kioskLoanService->loanMaxBooks();
    }

    public function activeLoanCount(User $user): int
    {
        return LoanItem::query()
            ->whereNull('returned_at', 'and', false)
            ->whereHas('loan', fn (Builder $query): Builder => $query
                ->whereBelongsTo($user)
                ->where('status', Loan::STATUS_BORROWED))
            ->count();
    }
}
