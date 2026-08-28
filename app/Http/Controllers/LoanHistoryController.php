<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanHistoryFilterRequest;
use App\Http\Resources\LoanItemResource;
use App\Models\Queries\LoanHistoryQuery;
use Inertia\Inertia;
use Inertia\Response;

class LoanHistoryController extends Controller
{
    public function __construct(
        protected LoanHistoryQuery $loanHistoryQuery,
    ) {}

    public function __invoke(LoanHistoryFilterRequest $request): Response
    {
        $user = $request->user();
        $filters = $request->filters();
        $loanItems = $this->loanHistoryQuery->paginate($user, $filters);

        return Inertia::render('loans/history', [
            'loans' => array_merge($loanItems->toArray(), [
                'data' => LoanItemResource::collection($loanItems->items())->resolve(),
            ]),
            'filters' => $filters,
            'stats' => $this->loanHistoryQuery->stats($user),
        ]);
    }
}
