<?php

namespace App\Http\Controllers;

use App\Http\Requests\Kiosk\ConsumeLoanDraftRequest;
use App\Services\LoanDraftService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class KioskLoanDraftController extends Controller
{
    public function __construct(
        protected LoanDraftService $loanDraftService,
    ) {}

    public function store(ConsumeLoanDraftRequest $request): RedirectResponse
    {
        $loan = $this->loanDraftService->consume($request->validatedPayload());
        $borrowedCount = $loan->items()->count();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "{$borrowedCount} buku berhasil dipinjam.",
        ]);

        return redirect()->route('kiosk.index', ['menu' => 'borrow']);
    }
}
