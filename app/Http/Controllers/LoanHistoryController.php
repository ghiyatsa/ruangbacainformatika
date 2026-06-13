<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanHistoryFilterRequest;
use App\Http\Resources\LoanItemResource;
use App\Models\Queries\LoanHistoryQuery;
use App\Models\ReturnDraft;
use App\Models\ReturnDraftItem;
use App\Services\ReturnDraftService;
use App\Support\AppTimezone;
use Inertia\Inertia;
use Inertia\Response;

class LoanHistoryController extends Controller
{
    public function __construct(
        protected LoanHistoryQuery $loanHistoryQuery,
        protected ReturnDraftService $returnDraftService,
    ) {}

    public function __invoke(LoanHistoryFilterRequest $request): Response
    {
        $user = $request->user();
        $filters = $request->filters();
        $draft = $this->returnDraftService->getCurrentDraft($user);
        $loanItems = $this->loanHistoryQuery->paginate($user, $filters);

        return Inertia::render('loans/history', [
            'loans' => array_merge($loanItems->toArray(), [
                'data' => LoanItemResource::collection($loanItems->items())->resolve(),
            ]),
            'filters' => $filters,
            'stats' => $this->loanHistoryQuery->stats($user),
            'returnDraft' => $this->toReturnDraftPayload($draft),
        ]);
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
