<?php

namespace App\Models\Queries;

use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class LoanHistoryQuery
{
    /**
     * @param  array{filter: string, search: string}  $filters
     * @return LengthAwarePaginator<int, LoanItem>
     */
    public function paginate(User $user, array $filters): LengthAwarePaginator
    {
        $loanItemsQuery = $user->loanItems();

        $this->applyFilters($loanItemsQuery, $filters);

        return $loanItemsQuery
            ->with([
                'loan:id,user_id,status,borrowed_at,due_at,returned_at',
                'bookItem:id,book_id,internal_code',
                'bookItem.book:id,title,slug',
            ])
            ->latest('loan_id')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * @return array{total: int, active: int, overdue: int, returned: int}
     */
    public function stats(User $user): array
    {
        $stats = LoanItem::query()
            ->join('loans', 'loans.id', '=', 'loan_items.loan_id')
            ->where('loans.user_id', $user->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN loan_items.returned_at IS NULL THEN 1 ELSE 0 END) as active')
            ->selectRaw('SUM(CASE WHEN loan_items.returned_at IS NOT NULL THEN 1 ELSE 0 END) as returned')
            ->selectRaw(
                'SUM(CASE WHEN loan_items.returned_at IS NULL AND loans.status = ? AND loans.due_at < ? THEN 1 ELSE 0 END) as overdue',
                [Loan::STATUS_BORROWED, now()]
            )
            ->first();

        return [
            'total' => (int) ($stats?->total ?? 0),
            'active' => (int) ($stats?->active ?? 0),
            'overdue' => (int) ($stats?->overdue ?? 0),
            'returned' => (int) ($stats?->returned ?? 0),
        ];
    }

    /**
     * @param  HasManyThrough<LoanItem, covariant User, *, *>  $loanItemsQuery
     * @param  array{filter: string, search: string}  $filters
     */
    protected function applyFilters(HasManyThrough $loanItemsQuery, array $filters): void
    {
        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $loanItemsQuery->where(function (Builder $query) use ($search): void {
                $query
                    ->whereHas('bookItem.book', function (Builder $bookQuery) use ($search): void {
                        $bookQuery->where('title', 'like', "%{$search}%");
                    })
                    ->orWhereHas('bookItem', function (Builder $bookItemQuery) use ($search): void {
                        $bookItemQuery->where('internal_code', 'like', "%{$search}%");
                    })
                    ->orWhere('loan_id', 'like', "%{$search}%");
            });
        }

        if ($filters['filter'] === 'overdue') {
            $loanItemsQuery
                ->whereNull('loan_items.returned_at')
                ->whereHas('loan', function (Builder $loanQuery): void {
                    $loanQuery
                        ->where('status', Loan::STATUS_BORROWED)
                        ->where('due_at', '<', now());
                });

            return;
        }

        if ($filters['filter'] === 'active') {
            $loanItemsQuery
                ->whereNull('loan_items.returned_at')
                ->whereHas('loan', function (Builder $loanQuery): void {
                    $loanQuery->where(function (Builder $statusQuery): void {
                        $statusQuery
                            ->where('status', '!=', Loan::STATUS_BORROWED)
                            ->orWhere('due_at', '>=', now());
                    });
                });

            return;
        }

        if ($filters['filter'] === 'returned') {
            $loanItemsQuery->whereNotNull('loan_items.returned_at');
        }
    }
}
