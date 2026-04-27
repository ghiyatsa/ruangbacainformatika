<?php

namespace App\Support\Kiosk;

use App\Models\KioskDevice;
use Illuminate\Contracts\Cookie\QueueingFactory as CookieFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

class KioskDeviceResolver
{
    public const DEVICE_HEADER = 'X-Kiosk-ID';

    public const DEVICE_NAME_HEADER = 'X-Kiosk-Name';

    public const DEVICE_COOKIE = 'kiosk_device_token';

    public function __construct(
        protected CookieFactory $cookieFactory,
    ) {}

    /**
     * @return array{device: KioskDevice, token: string}
     */
    public function resolveOrCreate(Request $request): array
    {
        $identifier = $this->identifierFromRequest($request);

        $device = KioskDevice::firstOrCreate(
            ['kiosk_identifier' => $identifier],
            [
                'name' => $this->deviceNameFromRequest($request),
                'registration_code' => $this->generateRegistrationCode(),
                'status' => KioskDevice::STATUS_PENDING,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_seen_at' => now(),
            ],
        );

        $device->forceFill([
            'name' => $device->name ?: $this->deviceNameFromRequest($request),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'last_seen_at' => now(),
        ])->save();

        $token = $request->cookie(self::DEVICE_COOKIE);

        if (! $device->hasValidToken($token)) {
            if ($device->isApproved()) {
                $device->markAsPending();
            }

            $token = $device->issueAccessToken();
        }

        return [
            'device' => $device,
            'token' => $token,
        ];
    }

    public function identifierFromRequest(Request $request): ?string
    {
        $identifier = $request->header(self::DEVICE_HEADER);

        if (! is_string($identifier)) {
            return null;
        }

        $normalizedIdentifier = Str::of($identifier)->trim()->limit(255, '')->toString();

        return $normalizedIdentifier !== '' ? $normalizedIdentifier : null;
    }

    public function deviceNameFromRequest(Request $request): ?string
    {
        $name = $request->header(self::DEVICE_NAME_HEADER);

        if (! is_string($name)) {
            return null;
        }

        $normalizedName = Str::of($name)->trim()->limit(255, '')->toString();

        return $normalizedName !== '' ? $normalizedName : null;
    }

    public function accessCookie(string $token): Cookie
    {
        return cookie(
            self::DEVICE_COOKIE,
            $token,
            60 * 24 * 365,
            '/',
            null,
            false,
            true,
            false,
            'lax',
        );
    }

    public function forgetAccessCookie(): void
    {
        $this->cookieFactory->queue($this->cookieFactory->forget(self::DEVICE_COOKIE));
    }

    protected function generateRegistrationCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (KioskDevice::query()->where('registration_code', $code)->exists());

        return $code;
    }
}
