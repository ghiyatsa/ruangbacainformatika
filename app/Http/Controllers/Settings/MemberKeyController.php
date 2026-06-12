<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\KioskBorrowVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberKeyController extends Controller
{
    public function __construct(
        protected KioskBorrowVerificationService $kioskBorrowVerificationService,
    ) {}

    public function show(Request $request): Response
    {
        return Inertia::render('settings/member-key', [
            'memberKey' => $this->ensureMemberKeyPayload($request),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $this->storeMemberKeySession($request);

        if (! $request->boolean('automatic')) {
            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Member key berhasil diperbarui.',
            ]);
        }

        return to_route('settings.member-key.show');
    }

    /**
     * @return array<string, mixed>
     */
    protected function ensureMemberKeyPayload(Request $request): array
    {
        $summary = $this->kioskBorrowVerificationService->current($request->user());
        $qr = session('member_qr');

        if (
            $summary === null
            || ! is_array($qr)
            || blank($qr['svg'] ?? null)
        ) {
            return $this->storeMemberKeySession($request);
        }

        return [
            'hasActiveQr' => true,
            'expiresAt' => $summary['expiresAt'],
            'expiresAtIso' => $summary['expiresAtIso'],
            'qrCodeSvg' => is_array($qr) ? ($qr['svg'] ?? null) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function storeMemberKeySession(Request $request): array
    {
        $verification = $this->kioskBorrowVerificationService->generate(
            $request->user(),
        );

        $request->session()->put('member_qr', [
            'payload' => $verification['payload'],
            'svg' => $verification['qr_svg'],
        ]);

        return [
            'hasActiveQr' => true,
            'expiresAt' => $verification['expires_at']->format('d F Y H:i'),
            'expiresAtIso' => $verification['expires_at']->toIso8601String(),
            'qrCodeSvg' => $verification['qr_svg'],
        ];
    }
}
