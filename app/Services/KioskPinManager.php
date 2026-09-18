<?php

namespace App\Services;

use App\Repositories\SettingRepository;
use App\Support\KioskIdlePolicy;

/**
 * Pengelola PIN kiosk dan konfigurasi sesi kiosk.
 *
 * Sejak UI web `/kiosk/*` dihapus, service ini hanya melayani API kiosk
 * (klien Flutter desktop): aktivasi perangkat via PIN dan penyajian
 * konfigurasi sesi. Alur sesi berbasis cookie/session sudah tidak ada —
 * perangkat API memakai `X-Kiosk-Device-Token`.
 */
class KioskPinManager
{
    /**
     * Nama cookie device token lama; masih dipakai untuk bucket rate limit
     * kiosk (lihat AppServiceProvider::kioskThrottleKey()).
     */
    public const COOKIE_DEVICE_TOKEN_KEY = 'kiosk_device_token';

    public function __construct(
        protected SettingRepository $settingRepository,
        protected KioskIdlePolicy $kioskIdlePolicy,
    ) {}

    public function isConfigured(): bool
    {
        return filled($this->currentPinHash());
    }

    public function canStartSession(): bool
    {
        return $this->kioskIdlePolicy->canStartSession();
    }

    /**
     * @return array{
     *     timezone: string,
     *     operatingOpenTime: string,
     *     operatingCloseTime: string,
     *     withinOperatingHours: bool,
     *     persistentForDevelopment: bool,
     *     sessionExpiresAtIso: string|null
     * }
     */
    public function sessionConfiguration(): array
    {
        return $this->kioskIdlePolicy->configuration();
    }

    public function currentPinHash(): ?string
    {
        $pinHash = $this->settingRepository->get('kiosk', 'pin_hash');

        return filled($pinHash) ? (string) $pinHash : null;
    }
}
