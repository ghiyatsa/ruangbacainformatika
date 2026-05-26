<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReturnDraftQrRequest;
use App\Services\ReturnDraftService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ReturnDraftController extends Controller
{
    public function __construct(
        protected ReturnDraftService $returnDraftService,
    ) {}

    public function generateQr(ReturnDraftQrRequest $request): RedirectResponse
    {
        $checkout = $this->returnDraftService->generateQr(
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
