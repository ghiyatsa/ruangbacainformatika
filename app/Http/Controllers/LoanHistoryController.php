<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoanResource;
use App\Models\Loan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoanHistoryController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $loansQuery = $user->loans();

        $stats = [
            'total' => $loansQuery->count(),
            'active' => (clone $loansQuery)
                ->where('status', Loan::STATUS_BORROWED)
                ->where('due_at', '>=', now())
                ->count(),
            'overdue' => (clone $loansQuery)
                ->where('status', Loan::STATUS_BORROWED)
                ->where('due_at', '<', now())
                ->count(),
            'returned' => (clone $loansQuery)
                ->where('status', Loan::STATUS_RETURNED)
                ->count(),
        ];

        $loans = $loansQuery
            ->with(['items.bookItem.book'])
            ->latest()
            ->paginate(10);

        return Inertia::render('loans/history', [
            'loans' => array_merge($loans->toArray(), [
                'data' => LoanResource::collection($loans->items())->resolve(),
            ]),
            'stats' => $stats,
        ]);
    }
}
