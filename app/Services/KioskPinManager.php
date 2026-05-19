<?php

namespace App\Services;

use App\Models\KioskDevice;
use App\Repositories\SettingRepository;
use App\Support\KioskNetworkGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class KioskPinManager
{
    public const SESSION_PIN_HASH_KEY = 'kiosk.pin_hash';

    public const SESSION_VERSION_KEY = 'kiosk.session_version';

    public const COOKIE_DEVICE_TOKEN_KEY = 'kiosk_device_token';

    public function __construct(
        protected SettingRepository $settingRepository,
        protected KioskNetworkGuard $kioskNetworkGuard,
    ) {}

    public function isConfigured(): bool
    {
        return filled($this->currentPinHash());
    }

    public function isVerified(Request $request): bool
    {
        $sessionPinHash = $request->session()->get(self::SESSION_PIN_HASH_KEY);
        $sessionVersion = (int) $request->session()->get(self::SESSION_VERSION_KEY, 0);
        $currentPinHash = $this->currentPinHash();

        $isSessionVerified = filled($sessionPinHash)
            && filled($currentPinHash)
            && $sessionVersion === $this->currentSessionVersion()
            && hash_equals($currentPinHash, (string) $sessionPinHash);

        if ($isSessionVerified && KioskDevice::query()->where('session_id', $request->session()->getId())->exists()) {
            return true;
        }

        // Try to verify via persistent cookie
        $deviceToken = $request->cookie(self::COOKIE_DEVICE_TOKEN_KEY);

        if ($deviceToken && $currentPinHash) {
            $device = KioskDevice::query()->where('device_token', $deviceToken)->first();

            if (
                $device
                && $device->network_scope !== null
                && $device->network_scope === $this->kioskNetworkGuard->networkScopeForRequest($request)
            ) {
                // Re-verify session
                $request->session()->put(self::SESSION_PIN_HASH_KEY, $currentPinHash);
                $request->session()->put(self::SESSION_VERSION_KEY, $this->currentSessionVersion());

                // Update device with new session ID
                $device->update([
                    'session_id' => $request->session()->getId(),
                    'last_active_at' => now(),
                    'ip_address' => $request->ip(),
                    'network_scope' => $this->kioskNetworkGuard->networkScopeForRequest($request),
                ]);

                return true;
            }
        }

        return false;
    }

    public function verify(string $pin, Request $request): bool
    {
        $currentPinHash = $this->currentPinHash();

        if (! filled($currentPinHash)) {
            return false;
        }

        if (! Hash::check($pin, $currentPinHash)) {
            return false;
        }

        $request->session()->regenerate();
        $request->session()->put(self::SESSION_PIN_HASH_KEY, $currentPinHash);
        $request->session()->put(self::SESSION_VERSION_KEY, $this->currentSessionVersion());

        $deviceToken = Str::random(64);

        KioskDevice::updateOrCreate(
            ['session_id' => $request->session()->getId()],
            [
                'device_token' => $deviceToken,
                'ip_address' => $request->ip(),
                'network_scope' => $this->kioskNetworkGuard->networkScopeForRequest($request),
                'user_agent' => $request->userAgent(),
                'last_active_at' => now(),
            ]
        );

        Cookie::queue(self::COOKIE_DEVICE_TOKEN_KEY, $deviceToken, 525600); // 1 year

        return true;
    }

    public function updateLastActive(Request $request): void
    {
        KioskDevice::query()->where('session_id', $request->session()->getId())
            ->update(['last_active_at' => now()]);
    }

    public function forget(Request $request): void
    {
        KioskDevice::query()->where('session_id', $request->session()->getId())->delete();

        Cookie::queue(Cookie::forget(self::COOKIE_DEVICE_TOKEN_KEY));

        $request->session()->forget([
            self::SESSION_PIN_HASH_KEY,
            self::SESSION_VERSION_KEY,
        ]);
    }

    public function currentPinHash(): ?string
    {
        $pinHash = $this->settingRepository->get('kiosk', 'pin_hash');

        return filled($pinHash) ? (string) $pinHash : null;
    }

    public function currentSessionVersion(): int
    {
        return max((int) $this->settingRepository->get('kiosk', 'session_version', 1), 1);
    }

    public function rotateSessions(): int
    {
        $nextVersion = $this->currentSessionVersion() + 1;

        $this->settingRepository->put('kiosk', 'session_version', $nextVersion);

        KioskDevice::query()->delete();

        return $nextVersion;
    }
}
