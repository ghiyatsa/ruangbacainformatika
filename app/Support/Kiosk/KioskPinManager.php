<?php

namespace App\Support\Kiosk;

use App\Support\Settings\SettingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KioskPinManager
{
    public const SESSION_PIN_HASH_KEY = 'kiosk.pin_hash';

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
        $currentPinHash = $this->currentPinHash();

        return filled($sessionPinHash)
            && filled($currentPinHash)
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

        return true;
    }

    public function forget(Request $request): void
    {
        $request->session()->forget(self::SESSION_PIN_HASH_KEY);
    }

    public function currentPinHash(): ?string
    {
        $pinHash = $this->settingRepository->get('kiosk', 'pin_hash');

        return filled($pinHash) ? (string) $pinHash : null;
    }
}
