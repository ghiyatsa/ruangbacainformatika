<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoanItemResource;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoanHistoryController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $loanItemsQuery = $user->loanItems();

        $stats = [
            'total' => $loanItemsQuery->count(),
            'active' => (clone $loanItemsQuery)
                ->whereNull('loan_items.returned_at')
                ->count(),
            'overdue' => (clone $loanItemsQuery)
                ->whereNull('loan_items.returned_at')
                ->whereHas('loan', fn (Builder $query): Builder => $query
                    ->where('status', Loan::STATUS_BORROWED)
                    ->where('due_at', '<', now()))
                ->count(),
            'returned' => (clone $loanItemsQuery)
                ->whereNotNull('loan_items.returned_at')
                ->count(),
        ];

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
        ]);
    }
}
