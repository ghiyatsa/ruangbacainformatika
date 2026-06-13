<?php

namespace App\Http\Controllers;

use App\Actions\Loans\GenerateReturnDraftQr;
use App\Http\Requests\ReturnDraftQrRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ReturnDraftController extends Controller
{
    public function generateQr(ReturnDraftQrRequest $request, GenerateReturnDraftQr $generateReturnDraftQr): RedirectResponse
    {
        $checkout = $generateReturnDraftQr->execute(
            $request->user(),
            $request->validatedLoanItemIds(),
        );

        session()->put('return_request_qr', [
            'payload' => $checkout['payload'],
            'svg' => $checkout['qr_svg'],
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'QR pengembalian berhasil dibuat.',
        ]);

        return redirect()->route('loans.history');
    }
}
