<?php

namespace App\Services;

use App\Repositories\SettingRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Kelola API key bersama untuk aplikasi kiosk (Flutter).
 *
 * Key dipakai oleh klien non-browser sebagai header `X-Kiosk-Api-Key`.
 * Yang disimpan di database hanya *hash*-nya (kiosk.api_key_hash), sehingga
 * plaintext tidak pernah bisa dibaca ulang — hanya ditampilkan sekali saat
 * dibuat/dirotasi.
 *
 * Logika ini dipakai bersama oleh perintah artisan `kiosk:api-key` dan
 * halaman pengaturan Filament, agar keduanya tidak pernah menyimpang.
 */
class KioskApiKeyManager
{
    public const SECTION = 'kiosk';

    public const HASH_KEY = 'api_key_hash';

    public const CREATED_AT_KEY = 'api_key_created_at';

    /**
     * Awalan key agar mudah dikenali saat dibaca manusia.
     */
    public const KEY_PREFIX = 'rbk_';

    public function __construct(
        protected SettingRepository $settingRepository,
    ) {}

    /**
     * Apakah API key sudah dikonfigurasi.
     */
    public function isConfigured(): bool
    {
        return filled($this->currentHash());
    }

    /**
     * Hash API key yang tersimpan (null bila belum ada).
     */
    public function currentHash(): ?string
    {
        $hash = $this->settingRepository->get(self::SECTION, self::HASH_KEY);

        return filled($hash) ? (string) $hash : null;
    }

    /**
     * Waktu pembuatan key terakhir (ISO string), bila tersedia.
     */
    public function createdAt(): ?string
    {
        $createdAt = $this->settingRepository->get(self::SECTION, self::CREATED_AT_KEY);

        return filled($createdAt) ? (string) $createdAt : null;
    }

    /**
     * Prefiks key untuk ditampilkan pada UI (mis. `rbk_a1b2c3d4…`).
     *
     * Hanya memakai hash sebagai bahan turunan — tidak pernah membocorkan key
     * asli, tetapi cukup untuk membedakan key yang satu dengan lainnya.
     */
    public function maskedPreview(): ?string
    {
        $hash = $this->currentHash();

        if ($hash === null) {
            return null;
        }

        // Turunan deterministik dari hash: 8 karakter heksadesimal.
        $derived = substr(hash('sha256', $hash), 0, 8);

        return self::KEY_PREFIX.$derived.'…';
    }

    /**
     * Buat API key baru dan kembalikan plaintext-nya.
     *
     * Key lama (bila ada) langsung berhenti berlaku karena hash-nya ditimpa.
     */
    public function generate(): string
    {
        $plainKey = self::KEY_PREFIX.Str::random(56);

        // Harus Hash::make (bcrypt) agar cocok dengan EnsureKioskDeviceTokenIsValid
        // yang memverifikasi memakai Hash::check().
        $this->settingRepository->put(self::SECTION, self::HASH_KEY, Hash::make($plainKey));
        $this->settingRepository->put(self::SECTION, self::CREATED_AT_KEY, now()->toIso8601String());

        return $plainKey;
    }

    /**
     * Cabut API key — seluruh akses API kiosk langsung ditolak.
     */
    public function revoke(): void
    {
        $this->settingRepository->forget(self::SECTION, self::HASH_KEY);
        $this->settingRepository->forget(self::SECTION, self::CREATED_AT_KEY);
    }
}
