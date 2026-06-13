<?php

namespace App\Actions\Kiosk;

use App\Models\Book;
use App\Models\Loan;
use App\Services\KioskLoanService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class SearchKioskBooks
{
    public function __construct(
        protected KioskLoanService $kioskLoanService,
    ) {}

    /**
     * @return EloquentCollection<int, Book>
     */
    public function execute(string $search, string $mode, string $memberIdentifier): EloquentCollection
    {
        if ($mode === 'return') {
            return $this->searchReturnableBooks($search, $memberIdentifier);
        }

        return $this->searchBorrowableBooks($search);
    }

    /**
     * @return EloquentCollection<int, Book>
     */
    protected function searchBorrowableBooks(string $search): EloquentCollection
    {
        return Book::query()
            ->search($search)
            ->where('is_borrowable', true)
            ->whereHas('items', fn ($query) => $query->available())
            ->with(['authors:id,name'])
            ->withCount('items')
            ->withCount([
                'items as available_items_count' => fn ($query) => $query->available(),
            ])
            ->orderBy('title')
            ->limit(8)
            ->get();
    }

    /**
     * @return EloquentCollection<int, Book>
     */
    protected function searchReturnableBooks(string $search, string $memberIdentifier): EloquentCollection
    {
        $member = filled($memberIdentifier)
            ? $this->kioskLoanService->findMemberByIdentifier($memberIdentifier)
            : null;

        if (! $member || ! $member->canBorrowBooks()) {
            return new EloquentCollection;
        }

        return Book::query()
            ->when($search !== '', fn ($query) => $query->search($search))
            ->whereHas('items.loanItems', function ($query) use ($member): void {
                $query
                    ->whereNull('returned_at', 'and', false)
                    ->whereHas('loan', fn ($loanQuery) => $loanQuery
                        ->whereBelongsTo($member)
                        ->where('status', Loan::STATUS_BORROWED));
            })
            ->with(['authors:id,name'])
            ->withCount('items')
            ->orderBy('title')
            ->limit(8)
            ->get();
    }
}
