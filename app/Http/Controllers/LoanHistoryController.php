<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoanItemResource;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\ReturnDraft;
use App\Models\ReturnDraftItem;
use App\Models\User;
use App\Services\ReturnDraftService;
use App\Support\AppTimezone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoanHistoryController extends Controller
{
    public function __construct(
        protected ReturnDraftService $returnDraftService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $filters = $this->validatedFilters($request);
        $loanItemsQuery = $user->loanItems();
        $draft = $this->returnDraftService->getCurrentDraft($user);
        $stats = $this->loanItemStats($user);

        $this->applyFilters($loanItemsQuery, $filters);

        $loanItems = $loanItemsQuery
            ->with([
                'loan:id,user_id,status,borrowed_at,due_at,returned_at',
                'bookItem:id,book_id,internal_code',
                'bookItem.book:id,title,slug',
            ])
            ->latest('loan_id')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('loans/history', [
            'loans' => array_merge($loanItems->toArray(), [
                'data' => LoanItemResource::collection($loanItems->items())->resolve(),
            ]),
            'filters' => $filters,
            'stats' => $stats,
            'returnDraft' => $this->toReturnDraftPayload($draft),
        ]);
    }

    /**
     * @return array{filter: string, search: string}
     */
    protected function validatedFilters(Request $request): array
    {
        /** @var array{filter?: string, search?: string} $validated */
        $validated = $request->validate([
            'filter' => ['nullable', 'in:all,overdue,active,returned'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        return [
            'filter' => $validated['filter'] ?? 'all',
            'search' => trim((string) ($validated['search'] ?? '')),
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

    /**
     * @return array{total: int, active: int, overdue: int, returned: int}
     */
    protected function loanItemStats(User $user): array
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
     * @return array<string, mixed>
     */
    protected function toReturnDraftPayload(?ReturnDraft $draft): array
    {
        $qr = session('return_request_qr');

        if (! $draft) {
            return [
                'id' => null,
                'status' => null,
                'itemsCount' => 0,
                'expiresAt' => null,
                'expiresAtIso' => null,
                'hasActiveQr' => false,
                'qrCodeSvg' => null,
                'selectedLoanItemIds' => [],
                'items' => [],
            ];
        }

        $draft->loadMissing([
            'items.loanItem.loan',
            'items.loanItem.bookItem.book',
        ]);

        return [
            'id' => $draft->id,
            'status' => $draft->status,
            'itemsCount' => $draft->items->count(),
            'expiresAt' => AppTimezone::format($draft->expires_at, 'd F Y H:i'),
            'expiresAtIso' => $draft->expires_at?->toIso8601String(),
            'hasActiveQr' => $draft->hasActiveToken(),
            'qrCodeSvg' => $draft->hasActiveToken() && is_array($qr) ? ($qr['svg'] ?? null) : null,
            'selectedLoanItemIds' => array_map('intval', $draft->selected_loan_item_ids ?? []),
            'items' => $draft->items
                ->sortBy('id')
                ->values()
                ->map(fn (ReturnDraftItem $item): array => [
                    'loanItemId' => $item->loan_item_id,
                    'bookTitle' => $item->loanItem->bookItem->book->title,
                    'internalCode' => $item->loanItem->bookItem->internal_code,
                    'borrowedAt' => AppTimezone::format($item->loanItem->loan?->borrowed_at, 'd F Y H:i'),
                    'dueAt' => AppTimezone::format($item->loanItem->loan?->due_at, 'd F Y H:i'),
                ])
                ->all(),
        ];
    }
}
