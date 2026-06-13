<?php

namespace App\Http\Controllers;

use App\Actions\Loans\ConsumeReturnDraft;
use App\Http\Requests\Kiosk\ConsumeReturnDraftRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class KioskReturnDraftController extends Controller
{
    public function store(ConsumeReturnDraftRequest $request, ConsumeReturnDraft $consumeReturnDraft): RedirectResponse
    {
        $returnedCount = $consumeReturnDraft->execute($request->validatedPayload());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "{$returnedCount} buku berhasil dikembalikan.",
        ]);

        return redirect()->route('kiosk.index', ['menu' => 'return']);
    }
}
