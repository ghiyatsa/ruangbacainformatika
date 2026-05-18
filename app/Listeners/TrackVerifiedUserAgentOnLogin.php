<?php

namespace App\Listeners;

use App\Models\User;
use App\Support\MailDeliveryIssue;
use Illuminate\Auth\Events\Login;
use Throwable;

class TrackVerifiedUserAgentOnLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        if (! $event->user instanceof User || ! $event->user->hasVerifiedEmail()) {
            return;
        }

        $request = request();

        if ($event->user->shouldTrustCurrentUserAgent($request->userAgent())) {
            $event->user->trustUserAgent($request->userAgent());

            return;
        }

        if (! $event->user->requiresEmailVerificationForUserAgent($request->userAgent())) {
            return;
        }

        $event->user->forceFill([
            'email_verified_at' => null,
        ])->saveQuietly();

        if (! $request->hasSession()) {
            return;
        }

        try {
            $event->user->sendEmailVerificationNotification();

            $request->session()->forget('auth.password_confirmed_at');
            $request->session()->put('verification_resend_available_at', now()->addSeconds(60)->timestamp);
            $request->session()->flash(
                'status',
                'Kami mendeteksi browser atau perangkat baru. OTP verifikasi sedang dikirim ke email Anda. '.MailDeliveryIssue::queuedNotice(),
            );
        } catch (Throwable $exception) {
            report($exception);

            $request->session()->flash(
                'status',
                MailDeliveryIssue::verificationMessage($exception),
            );
        }
    }
}
