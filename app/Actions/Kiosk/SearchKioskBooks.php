<?php

declare(strict_types=1);

namespace App\Actions\Kiosk;

use App\Models\Book;
use App\Models\Loan;
use App\Services\KioskLoanService;
use App\Services\Search\BookSearchRanker;
use App\Services\Search\SearchTermResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Pencarian buku di kiosk, memakai mesin yang sama dengan pencarian global web:
 * ranking relevansi dan koreksi ejaan.
 *
 * Koreksi ejaan berjalan senyap — hasil tetap benar saat pengguna salah ketik,
 * tetapi kiosk tidak menampilkan saran kata kunci maupun pemberitahuan koreksi
 * karena perangkat publik harus tetap sesederhana mungkin.
 */
class SearchKioskBooks
{
    public function __construct(
        protected KioskLoanService $kioskLoanService,
        protected BookSearchRanker $ranker,
        protected SearchTermResolver $terms,
    ) {}

    public function execute(string $search, string $mode, string $memberIdentifier): KioskBookSearchResult
    {
        if ($mode === 'return') {
            return new KioskBookSearchResult(
                $this->searchReturnableBooks($search, $memberIdentifier),
            );
        }

        return $this->searchBorrowableBooks($search);
    }

    protected function searchBorrowableBooks(string $search): KioskBookSearchResult
    {
        // Koreksi ejaan hanya bila hasil asli terlalu sedikit, sehingga
        // pencarian yang sudah baik tidak pernah memburuk.
        $resolved = $this->terms->resolve(
            $search,
            fn (string $term): int => $this->borrowableQuery($term)->count(),
        );

        $books = $this->borrowableQuery($resolved)
            ->tap(fn (Builder $query) => $this->ranker->apply($query, $resolved))
            ->with(['authors:id,name'])
            ->withCount('items')
            ->withCount([
                'items as available_items_count' => fn (Builder $query) => $query->available(),
            ])
            ->limit(8)
            ->get();

        return new KioskBookSearchResult(books: $books);
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
            ->when($search !== '', fn (Builder $query) => $query->search($search))
            ->whereHas('items.loanItems', function (Builder $query) use ($member): void {
                $query
                    ->whereNull('returned_at', 'and', false)
                    ->whereHas('loan', fn (Builder $loanQuery) => $loanQuery
                        ->whereBelongsTo($member)
                        ->where('status', Loan::STATUS_BORROWED));
            })
            ->with(['authors:id,name'])
            ->withCount('items')
            ->orderBy('title')
            ->limit(8)
            ->get();
    }

    /**
     * @return Builder<Book>
     */
    protected function borrowableQuery(string $search): Builder
    {
        return Book::query()
            ->select('books.*')
            ->search($search)
            ->where('is_borrowable', true)
            ->whereHas('items', fn (Builder $query) => $query->available());
    }
}
