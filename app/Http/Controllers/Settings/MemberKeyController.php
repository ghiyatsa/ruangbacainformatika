<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
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
            'memberKey' => $this->getMemberKeyPayload($request->user()),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $this->kioskBorrowVerificationService->generate($request->user());

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
    public function getMemberKeyPayload(User $user): array
    {
        $summary = $this->kioskBorrowVerificationService->current($user);

        if ($summary === null || blank($summary['qrCodeSvg'] ?? null)) {
            $verification = $this->kioskBorrowVerificationService->generate($user);

            return [
                'hasActiveQr' => true,
                'expiresAt' => $verification['expires_at']->format('d F Y H:i'),
                'expiresAtIso' => $verification['expires_at']->toIso8601String(),
                'qrCodeSvg' => $verification['qr_svg'],
            ];
        }

        return [
            'hasActiveQr' => true,
            'expiresAt' => $summary['expiresAt'],
            'expiresAtIso' => $summary['expiresAtIso'],
            'qrCodeSvg' => $summary['qrCodeSvg'],
        ];
    }
}
