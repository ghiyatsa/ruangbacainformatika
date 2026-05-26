<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoanItemResource;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\ReturnDraft;
use App\Models\ReturnDraftItem;
use App\Models\User;
use App\Services\ReturnDraftService;
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
        $loanItemsQuery = $user->loanItems();
        $draft = $this->returnDraftService->getCurrentDraft($user);
        $stats = $this->loanItemStats($user);

        $loanItems = $loanItemsQuery
            ->with([
                'loan:id,user_id,status,borrowed_at,due_at,returned_at',
                'bookItem:id,book_id,internal_code',
                'bookItem.book:id,title,slug',
            ])
            ->latest('loan_id')
            ->latest('id')
            ->paginate(15);

        return Inertia::render('loans/history', [
            'loans' => array_merge($loanItems->toArray(), [
                'data' => LoanItemResource::collection($loanItems->items())->resolve(),
            ]),
            'stats' => $stats,
            'returnDraft' => $this->toReturnDraftPayload($draft),
        ]);
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
            'expiresAt' => $draft->expires_at?->translatedFormat('d F Y H:i'),
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
                    'borrowedAt' => $item->loanItem->loan?->borrowed_at?->translatedFormat('d F Y H:i') ?? '-',
                    'dueAt' => $item->loanItem->loan?->due_at?->translatedFormat('d F Y H:i') ?? '-',
                ])
                ->all(),
        ];
    }
}
