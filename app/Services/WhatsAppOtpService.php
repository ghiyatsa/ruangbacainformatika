<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\WhatsAppOtpNotification;
use App\Support\CampusEmail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class WhatsAppOtpService
{
    protected const OTP_TTL_SECONDS = 600;

    protected const RESEND_COOLDOWN_SECONDS = 60;

    protected const SEND_LIMIT_PER_HOUR = 5;

    protected const VERIFY_LIMIT = 5;

    public function __construct(
        protected CampusEmail $campusEmail,
        protected MemberRegistrationClaimService $memberRegistrationClaimService,
    ) {}

    /**
     * @return array{
     *     maskedWhatsapp: string|null,
     *     hasActiveChallenge: bool,
     *     expiresIn: int,
     *     resendAvailableIn: int,
     *     approvalMode: 'automatic'|'manual',
     *     approvalMessage: string
     * }
     */
    public function ensureChallenge(User $user): array
    {
        $status = $this->status($user);

        if ($status['hasActiveChallenge']) {
            return $status;
        }

        return $this->dispatch($user);
    }

    /**
     * @return array{
     *     maskedWhatsapp: string|null,
     *     hasActiveChallenge: bool,
     *     expiresIn: int,
     *     resendAvailableIn: int,
     *     approvalMode: 'automatic'|'manual',
     *     approvalMessage: string
     * }
     */
    public function dispatch(User $user): array
    {
        $this->ensureVerificationIsRequired($user);

        if (RateLimiter::tooManyAttempts($this->hourlySendKey($user), self::SEND_LIMIT_PER_HOUR)) {
            $seconds = RateLimiter::availableIn($this->hourlySendKey($user));

            throw ValidationException::withMessages([
                'otp' => "Batas kirim tercapai. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (RateLimiter::tooManyAttempts($this->cooldownKey($user), 1)) {
            $seconds = RateLimiter::availableIn($this->cooldownKey($user));

            throw ValidationException::withMessages([
                'otp' => "Tunggu {$seconds} detik sebelum mengirim ulang.",
            ]);
        }

        if (! app(WhatsAppGateway::class)->configured()) {
            throw new RuntimeException('Gateway WhatsApp belum dikonfigurasi.');
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $now = now();

        $challenge = [
            'hash' => $this->hashCode($code),
            'phone' => $user->whatsapp,
            'expires_at' => $now->copy()->addSeconds(self::OTP_TTL_SECONDS)->timestamp,
        ];

        $attempts = RateLimiter::attempts($this->hourlySendKey($user));
        $cooldownSeconds = self::RESEND_COOLDOWN_SECONDS * ($attempts + 1);

        $user->notify(new WhatsAppOtpNotification($code));

        Cache::put($this->challengeKey($user), $challenge, $now->copy()->addSeconds(self::OTP_TTL_SECONDS));
        RateLimiter::hit($this->cooldownKey($user), $cooldownSeconds);
        RateLimiter::hit($this->hourlySendKey($user), 3600);

        return $this->status($user);
    }

    /**
     * @return array{autoApproved: bool, approvalPending: bool}
     */
    public function verify(User $user, string $code): array
    {
        $this->ensureVerificationIsRequired($user);

        $challenge = Cache::get($this->challengeKey($user));

        if (! is_array($challenge)) {
            throw ValidationException::withMessages([
                'code' => 'Kode tidak ditemukan atau sudah kedaluwarsa. Kirim ulang.',
            ]);
        }

        if (RateLimiter::tooManyAttempts($this->verifyKey($user), self::VERIFY_LIMIT)) {
            $seconds = RateLimiter::availableIn($this->verifyKey($user));

            throw ValidationException::withMessages([
                'code' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (($challenge['phone'] ?? null) !== $user->whatsapp || ($challenge['expires_at'] ?? 0) < now()->timestamp) {
            Cache::forget($this->challengeKey($user));

            throw ValidationException::withMessages([
                'code' => 'Kode sudah kedaluwarsa. Kirim ulang.',
            ]);
        }

        if (! hash_equals((string) ($challenge['hash'] ?? ''), $this->hashCode($code))) {
            RateLimiter::hit($this->verifyKey($user), self::OTP_TTL_SECONDS);

            throw ValidationException::withMessages([
                'code' => 'Kode tidak sesuai.',
            ]);
        }

        Cache::forget($this->challengeKey($user));
        RateLimiter::clear($this->verifyKey($user));
        RateLimiter::clear($this->cooldownKey($user));

        $autoApproved = $this->campusEmail->shouldAutoApprove($user->email);

        $user->forceFill([
            'whatsapp_verified_at' => now(),
            'is_approved' => $user->is_approved || $autoApproved,
        ])->save();

        if ($user->hasRequiredProfileDetails() && ! $user->hasCompletedProfile()) {
            $user->markProfileAsCompleted();
        }

        $this->memberRegistrationClaimService->markLinkedClaimAsVerified($user);

        return [
            'autoApproved' => $autoApproved,
            'approvalPending' => $user->usesCampusEmail() && ! ($user->is_approved || $autoApproved),
        ];
    }

    /**
     * @return array{
     *     maskedWhatsapp: string|null,
     *     hasActiveChallenge: bool,
     *     expiresIn: int,
     *     resendAvailableIn: int,
     *     approvalMode: 'automatic'|'manual',
     *     approvalMessage: string
     * }
     */
    public function status(User $user): array
    {
        $challenge = Cache::get($this->challengeKey($user));
        $expiresAt = is_array($challenge) ? (int) ($challenge['expires_at'] ?? 0) : 0;
        $expiresIn = max(0, $expiresAt - now()->timestamp);
        $resendAvailableIn = RateLimiter::tooManyAttempts($this->cooldownKey($user), 1)
            ? RateLimiter::availableIn($this->cooldownKey($user))
            : 0;
        $autoApproval = $this->campusEmail->shouldAutoApprove($user->email);

        return [
            'maskedWhatsapp' => $this->maskPhoneNumber($user->whatsapp),
            'hasActiveChallenge' => $expiresIn > 0,
            'expiresIn' => $expiresIn,
            'resendAvailableIn' => $resendAvailableIn,
            'approvalMode' => $autoApproval ? 'automatic' : 'manual',
            'approvalMessage' => $autoApproval
                ? 'Verifikasi WhatsApp akan melengkapi status anggota Anda.'
                : 'Setelah verifikasi WhatsApp, akun Anda akan menunggu persetujuan admin.',
        ];
    }

    protected function ensureVerificationIsRequired(User $user): void
    {
        if (! $user->requiresWhatsAppVerification()) {
            throw ValidationException::withMessages([
                'otp' => 'Verifikasi tidak diperlukan untuk akun ini.',
            ]);
        }

        if (! filled($user->whatsapp)) {
            throw ValidationException::withMessages([
                'whatsapp' => 'Masukkan nomor WhatsApp terlebih dahulu.',
            ]);
        }
    }

    protected function challengeKey(User $user): string
    {
        return 'whatsapp-otp:challenge:'.$user->id;
    }

    protected function cooldownKey(User $user): string
    {
        return 'whatsapp-otp:cooldown:'.$user->id;
    }

    protected function hourlySendKey(User $user): string
    {
        return 'whatsapp-otp:hourly:'.$user->id;
    }

    protected function verifyKey(User $user): string
    {
        return 'whatsapp-otp:verify:'.$user->id;
    }

    protected function hashCode(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    protected function maskPhoneNumber(?string $phoneNumber): ?string
    {
        if (! is_string($phoneNumber) || $phoneNumber === '') {
            return null;
        }

        if (strlen($phoneNumber) <= 6) {
            return $phoneNumber;
        }

        return Str::substr($phoneNumber, 0, 4)
            .str_repeat('*', max(0, strlen($phoneNumber) - 6))
            .Str::substr($phoneNumber, -2);
    }
}
