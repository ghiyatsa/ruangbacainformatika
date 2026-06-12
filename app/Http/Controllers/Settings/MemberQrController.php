<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\KioskBorrowVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberQrController extends Controller
{
    public function __construct(
        protected KioskBorrowVerificationService $kioskBorrowVerificationService,
    ) {}

    public function show(Request $request): Response
    {
        return Inertia::render('settings/member-qr', [
            'memberQr' => $this->toMemberQrPayload($request),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $verification = $this->kioskBorrowVerificationService->generate(
            $request->user(),
        );

        session()->put('member_qr', [
            'payload' => $verification['payload'],
            'svg' => $verification['qr_svg'],
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'QR anggota berhasil dibuat.',
        ]);

        return to_route('settings.member-qr.show');
    }

    /**
     * @return array<string, mixed>
     */
    protected function toMemberQrPayload(Request $request): array
    {
        $summary = $this->kioskBorrowVerificationService->current($request->user());
        $qr = session('member_qr');

        if ($summary === null) {
            $request->session()->forget('member_qr');

            return [
                'hasActiveQr' => false,
                'expiresAt' => null,
                'expiresAtIso' => null,
                'qrCodeSvg' => null,
            ];
        }

        return [
            'hasActiveQr' => true,
            'expiresAt' => $summary['expiresAt'],
            'expiresAtIso' => $summary['expiresAtIso'],
            'qrCodeSvg' => is_array($qr) ? ($qr['svg'] ?? null) : null,
        ];
    }
}
