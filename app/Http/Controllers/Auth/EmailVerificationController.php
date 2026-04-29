<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Responses\Auth\RegisterResponse;
use App\Support\Auth\AuthenticationRedirector;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationController extends Controller
{
    protected const RESEND_COOLDOWN_SECONDS = 60;

    public function __construct(
        protected AuthenticationRedirector $authenticationRedirector,
    ) {}

    public function notice(Request $request): Response|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            if ($request->session()->pull(RegisterResponse::KIOSK_RETURN_AFTER_VERIFICATION_KEY, false)) {
                return redirect()->route('kiosk.index');
            }

            return $this->authenticationRedirector->redirectResponse($request);
        }

        return Inertia::render('auth/verify-email');
    }

    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->authenticationRedirector->redirectResponse($request);
        }

        $request->user()->sendEmailVerificationNotification();
        $request->session()->put(
            'verification_resend_available_at',
            now()->addSeconds(self::RESEND_COOLDOWN_SECONDS)->timestamp,
        );

        return redirect()
            ->route('verification.notice')
            ->with('status', 'Link verifikasi telah dikirim ulang ke email Anda.');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->authenticationRedirector->redirectResponse($request);
        }

        $request->fulfill();

        if ($request->session()->pull(RegisterResponse::KIOSK_RETURN_AFTER_VERIFICATION_KEY, false)) {
            return redirect()
                ->route('kiosk.index')
                ->with('success', 'Email berhasil diverifikasi. Silakan lanjut menggunakan kiosk.');
        }

        return $this->authenticationRedirector
            ->redirectResponse($request)
            ->with('status', 'Email berhasil diverifikasi.');
    }
}
