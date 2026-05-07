<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoanResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoanHistoryController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $loans = $request->user()
            ->loans()
            ->with(['items.bookItem.book'])
            ->latest()
            ->paginate(10);

        return Inertia::render('loans/history', [
            'loans' => array_merge($loans->toArray(), [
                'data' => LoanResource::collection($loans->items())->resolve(),
            ]),
        ]);
    }
}
