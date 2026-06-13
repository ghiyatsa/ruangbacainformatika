<?php

namespace App\Http\Controllers;

use App\Actions\Loans\ConsumeLoanDraft;
use App\Http\Requests\Kiosk\ConsumeLoanDraftRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class KioskLoanDraftController extends Controller
{
    public function store(ConsumeLoanDraftRequest $request, ConsumeLoanDraft $consumeLoanDraft): RedirectResponse
    {
        $loan = $consumeLoanDraft->execute($request->validatedPayload());
        $borrowedCount = $loan->items()->count();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "{$borrowedCount} buku berhasil dipinjam.",
        ]);

        return redirect()->route('kiosk.index', ['menu' => 'borrow']);
    }
}
