<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\KioskDevice;
use App\Repositories\SettingRepository;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autentikasi perangkat kiosk untuk klien non-browser (aplikasi Flutter).
 *
 * Menerima dua bentuk kredensial:
 *
 *  1. API key bersama (utama, untuk Flutter)
 *     Header: X-Kiosk-Api-Key
 *     Disimpan sebagai hash di settings (kiosk.api_key_hash). Berlaku permanen
 *     sampai dirotasi dari server — tidak perlu memasukkan PIN di aplikasi.
 *
 *  2. Device token (kompatibilitas)
 *     Header: X-Kiosk-Device-Token
 *     Diterbitkan oleh endpoint aktivasi, berlaku 24 jam sejak aktivitas terakhir.
 *
 * Middleware ini tidak bergantung pada session web, sehingga aman dipakai
 * oleh klien non-browser.
 */
class EnsureKioskDeviceTokenIsValid
{
    public const API_KEY_HEADER = 'X-Kiosk-Api-Key';

    public const DEVICE_TOKEN_HEADER = 'X-Kiosk-Device-Token';

    /**
     * Masa berlaku device token sejak aktivitas terakhir perangkat.
     */
    public const TOKEN_TTL_HOURS = 24;

    public function __construct(
        protected SettingRepository $settingRepository,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $device = $this->resolveDeviceToken($request);

        if (! $device instanceof KioskDevice && ! $this->hasValidApiKey($request)) {
            return $this->unauthorized();
        }

        if ($device instanceof KioskDevice) {
            $device->forceFill([
                'last_active_at' => now(),
                'ip_address' => $request->ip(),
            ])->save();

            $request->attributes->set('kiosk_device', $device);
        }

        return $next($request);
    }

    /**
     * Verifikasi API key bersama terhadap hash yang tersimpan.
     */
    protected function hasValidApiKey(Request $request): bool
    {
        $providedKey = $request->header(self::API_KEY_HEADER);

        if (! is_string($providedKey) || trim($providedKey) === '') {
            return false;
        }

        $storedHash = $this->settingRepository->get('kiosk', 'api_key_hash');

        if (! is_string($storedHash) || $storedHash === '') {
            return false;
        }

        return Hash::check(trim($providedKey), $storedHash);
    }

    /**
     * Resolve perangkat kiosk dari header device token (opsional).
     */
    protected function resolveDeviceToken(Request $request): ?KioskDevice
    {
        $token = $request->header(self::DEVICE_TOKEN_HEADER);

        if (! is_string($token) || trim($token) === '') {
            return null;
        }

        $device = KioskDevice::query()
            ->where('device_token', trim($token))
            ->first();

        if (! $device instanceof KioskDevice) {
            return null;
        }

        $lastActiveAt = $device->last_active_at;

        if (
            $lastActiveAt === null
            || $lastActiveAt->lessThanOrEqualTo(now()->subHours(self::TOKEN_TTL_HOURS))
        ) {
            $device->delete();

            return null;
        }

        return $device;
    }

    protected function unauthorized(): JsonResponse
    {
        return response()->json([
            'message' => 'Perangkat kiosk tidak terautentikasi.',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
