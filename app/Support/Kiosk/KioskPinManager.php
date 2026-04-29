<?php

namespace App\Support\Kiosk;

use App\Support\Settings\SettingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KioskPinManager
{
    public const SESSION_PIN_HASH_KEY = 'kiosk.pin_hash';

    public const SESSION_VERSION_KEY = 'kiosk.session_version';

    public function __construct(
        protected SettingRepository $settingRepository,
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

        return filled($sessionPinHash)
            && filled($currentPinHash)
            && $sessionVersion === $this->currentSessionVersion()
            && hash_equals($currentPinHash, (string) $sessionPinHash);
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

        return true;
    }

    public function forget(Request $request): void
    {
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

        return $nextVersion;
    }
}
