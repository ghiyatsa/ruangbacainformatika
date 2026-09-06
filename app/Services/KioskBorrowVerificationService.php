<?php

namespace App\Services;

use App\Models\User;
use App\Support\QrCodeWithLogo;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KioskBorrowVerificationService
{
    public const TOKEN_PREFIX = 'RB-KIOSK-BORROW-VERIFY-';

    public const SHORT_TOKEN_PREFIX = 'MK-';

    protected const OPAQUE_TOKEN_LENGTH = 48;

    protected const EXPIRY_MINUTES = 1;

    /**
     * @return array{payload: string, qr_svg: string, expires_at: CarbonImmutable}
     */
    public function generate(User $user): array
    {
        $this->clearExistingForUser($user);

        $payload = $this->makeToken();
        $expiresAt = now()->addMinutes(self::EXPIRY_MINUTES);
        $qrSvg = $this->generateQrSvg($payload);

        Cache::put($this->userCacheKey($user), [
            'token_hash' => hash('sha256', $payload),
            'payload' => $payload,
            'qr_svg' => $qrSvg,
            'expires_at' => $expiresAt->toIso8601String(),
        ], $expiresAt);

        Cache::put($this->tokenCacheKey($payload), [
            'user_id' => $user->getKey(),
            'expires_at' => $expiresAt->toIso8601String(),
        ], $expiresAt);

        return [
            'payload' => $payload,
            'qr_svg' => $qrSvg,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * @return array{expiresAt: string, expiresAtIso: string, qrCodeSvg: string|null}|null
     */
    public function current(User $user): ?array
    {
        $cached = Cache::get($this->userCacheKey($user));

        if (! is_array($cached)) {
            return null;
        }

        $expiresAt = CarbonImmutable::parse((string) ($cached['expires_at'] ?? ''));

        if ($expiresAt->isPast()) {
            $this->clearExistingForUser($user);

            return null;
        }

        return [
            'expiresAt' => $expiresAt->format('d F Y H:i'),
            'expiresAtIso' => $expiresAt->toIso8601String(),
            'qrCodeSvg' => $cached['qr_svg'] ?? null,
        ];
    }

    public function consume(string $payload): User
    {
        $user = $this->resolveUser($payload);
        $token = $this->extractToken($payload);

        if ($token !== null) {
            $this->forgetToken($token, $user->getKey());
        }

        return $user;
    }

    public function resolveUser(string $payload): User
    {
        $token = $this->extractToken($payload);

        if ($token === null) {
            throw ValidationException::withMessages([
                'verification_payload' => 'QR verifikasi peminjaman tidak valid.',
            ]);
        }

        $cached = Cache::get($this->tokenCacheKey($token));

        if (! is_array($cached)) {
            throw ValidationException::withMessages([
                'verification_payload' => 'QR verifikasi peminjaman tidak ditemukan atau sudah dipakai.',
            ]);
        }

        $expiresAt = CarbonImmutable::parse((string) ($cached['expires_at'] ?? ''));

        if ($expiresAt->isPast()) {
            $this->forgetToken($token, (int) ($cached['user_id'] ?? 0));

            throw ValidationException::withMessages([
                'verification_payload' => 'QR verifikasi peminjaman sudah kedaluwarsa. Buat ulang dari akun anggota.',
            ]);
        }

        $user = User::query()->find((int) ($cached['user_id'] ?? 0));

        if (! $user instanceof User) {
            $this->forgetToken($token, (int) ($cached['user_id'] ?? 0));

            throw ValidationException::withMessages([
                'verification_payload' => 'Akun untuk QR verifikasi ini tidak ditemukan.',
            ]);
        }

        return $user;
    }

    protected function clearExistingForUser(User $user): void
    {
        $cached = Cache::get($this->userCacheKey($user));

        if (is_array($cached) && isset($cached['token_hash'])) {
            Cache::forget($this->tokenHashCacheKey((string) $cached['token_hash']));
        }

        Cache::forget($this->userCacheKey($user));
    }

    protected function forgetToken(string $token, int $userId): void
    {
        Cache::forget($this->tokenCacheKey($token));

        if ($userId > 0) {
            Cache::forget($this->userCacheKey($userId));
        }
    }

    protected function userCacheKey(User|int $user): string
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return "kiosk-borrow-verification:user:{$userId}";
    }

    protected function tokenCacheKey(string $payload): string
    {
        return $this->tokenHashCacheKey(hash('sha256', $payload));
    }

    protected function tokenHashCacheKey(string $tokenHash): string
    {
        return "kiosk-borrow-verification:token:{$tokenHash}";
    }

    protected function extractToken(string $payload): ?string
    {
        $normalized = Str::of($payload)->trim()->toString();

        if ($normalized === '') {
            return null;
        }

        if ($this->isReadableToken($normalized)) {
            return $normalized;
        }

        if (filter_var($normalized, FILTER_VALIDATE_URL) !== false) {
            $query = parse_url($normalized, PHP_URL_QUERY);

            if (! is_string($query)) {
                return null;
            }

            parse_str($query, $queryParams);

            $token = $queryParams['token'] ?? null;

            return is_string($token) && $this->isReadableToken($token)
                ? $token
                : null;
        }

        return null;
    }

    protected function makeToken(): string
    {
        return self::SHORT_TOKEN_PREFIX.Str::random(self::OPAQUE_TOKEN_LENGTH);
    }

    protected function isReadableToken(string $token): bool
    {
        if (Str::startsWith($token, [self::TOKEN_PREFIX, self::SHORT_TOKEN_PREFIX])) {
            return true;
        }

        return preg_match('/\A[A-Za-z0-9]{80,160}\z/', $token) === 1;
    }

    protected function generateQrSvg(string $payload): string
    {
        return QrCodeWithLogo::generateSvg(
            payload: $payload,
            size: 192,
            withLogo: true,
            fillColor: 'currentColor',
            bgColor: 'transparent'
        );
    }
}
