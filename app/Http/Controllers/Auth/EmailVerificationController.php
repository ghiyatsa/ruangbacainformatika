<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Responses\Auth\RegisterResponse;
use App\Services\Auth\AuthenticationRedirector;
use App\Support\MailDeliveryIssue;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

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

        $availableAt = $request->session()->get('verification_resend_available_at');

        if ($availableAt && now()->timestamp < $availableAt) {
            $secondsRemaining = $availableAt - now()->timestamp;

            return back()->withErrors([
                'resend' => "Tunggu {$secondsRemaining} detik lagi sebelum mengirim ulang OTP.",
            ]);
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'resend' => MailDeliveryIssue::verificationMessage($exception),
            ]);
        }

        $request->session()->put(
            'verification_resend_available_at',
            now()->addSeconds(self::RESEND_COOLDOWN_SECONDS)->timestamp,
        );

        return redirect()
            ->route('verification.notice')
            ->with('status', 'OTP verifikasi sedang dikirim ke email Anda. '.MailDeliveryIssue::queuedNotice());
    }

    public function verify(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->authenticationRedirector->redirectResponse($request);
        }

        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $cachedOtp = Cache::get("email_verification_otp_{$request->user()->id}");

        if (! $cachedOtp || $cachedOtp != $request->otp) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid atau sudah kadaluarsa.']);
        }

        $request->user()->markEmailAsVerified();
        Cache::forget("email_verification_otp_{$request->user()->id}");

        event(new Verified($request->user()));

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
