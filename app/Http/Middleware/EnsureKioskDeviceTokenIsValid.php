<?php

namespace App\Http\Middleware;

use App\Models\KioskDevice;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autentikasi perangkat kiosk berbasis device token (tanpa session web).
 *
 * Berbeda dengan EnsureKioskPinIsValid yang bergantung pada session + cookie,
 * middleware ini membaca header X-Kiosk-Device-Token sehingga dapat dipakai
 * oleh klien non-browser (aplikasi Flutter) maupun kiosk web.
 */
class EnsureKioskDeviceTokenIsValid
{
    public const HEADER = 'X-Kiosk-Device-Token';

    /**
     * Masa berlaku token sejak aktivitas terakhir perangkat.
     */
    public const TOKEN_TTL_HOURS = 24;

    public function handle(Request $request, Closure $next): Response
    {
        $device = $this->resolveDevice($request);

        if (! $device instanceof KioskDevice) {
            return $this->unauthorized($request);
        }

        $device->forceFill([
            'last_active_at' => now(),
            'ip_address' => $request->ip(),
        ])->save();

        $request->attributes->set('kiosk_device', $device);

        return $next($request);
    }

    /**
     * Resolve perangkat kiosk dari header token, dengan toleransi jam operasional
     * dan masa berlaku.
     */
    protected function resolveDevice(Request $request): ?KioskDevice
    {
        $token = $this->tokenFrom($request);

        if ($token === null) {
            return null;
        }

        $device = KioskDevice::query()
            ->where('device_token', $token)
            ->first();

        if (! $device instanceof KioskDevice) {
            return null;
        }

        $lastActiveAt = $device->last_active_at;

        if (
            $lastActiveAt === null
            || $lastActiveAt->lessThanOrEqualTo(now()->subHours(self::TOKEN_TTL_HOURS))
        ) {
            // Token kedaluwarsa — hapus agar tidak dapat dipakai ulang.
            $device->delete();

            return null;
        }

        return $device;
    }

    protected function tokenFrom(Request $request): ?string
    {
        $token = $request->header(self::HEADER)
            ?? $request->bearerToken();

        if (! is_string($token)) {
            return null;
        }

        $token = trim($token);

        return $token !== '' ? $token : null;
    }

    protected function unauthorized(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Perangkat kiosk tidak terautentikasi.',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
