<?php

namespace App\Http\Controllers;

use App\Http\Requests\Kiosk\ConsumeReturnDraftRequest;
use App\Services\ReturnDraftService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class KioskReturnDraftController extends Controller
{
    public function __construct(
        protected ReturnDraftService $returnDraftService,
    ) {}

    public function store(ConsumeReturnDraftRequest $request): RedirectResponse
    {
        $returnedCount = $this->returnDraftService->consume($request->validatedPayload());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "{$returnedCount} buku berhasil dikembalikan.",
        ]);

        return redirect()->route('kiosk.index', ['menu' => 'return']);
    }
}
