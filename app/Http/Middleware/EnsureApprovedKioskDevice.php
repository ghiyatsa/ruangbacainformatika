<?php

namespace App\Http\Middleware;

use App\Models\KioskDevice;
use App\Support\Kiosk\KioskDeviceResolver;
use App\Support\Kiosk\KioskPinManager;
use App\Support\Settings\SettingRepository;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedKioskDevice
{
    public function __construct(
        protected KioskDeviceResolver $kioskDeviceResolver,
        protected KioskPinManager $kioskPinManager,
        protected SettingRepository $settingRepository,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $siteSettings = $this->settingRepository->sectionValues('general', [
            'site_name' => config('app.name'),
            'site_tagline' => 'Sistem pendataan pengunjung',
        ]);

        $kioskSettings = $this->settingRepository->sectionValues('kiosk', [
            'title' => 'Pendataan Pengunjung Perpustakaan',
            'subtitle' => 'Silakan masukkan PIN untuk mengaktifkan perangkat kiosk.',
            'waiting_message' => 'Perangkat kiosk harus mengirim header identitas agar dapat mendaftar ke sistem.',
            'waiting_refresh_seconds' => 20,
            'success_redirect_seconds' => 6,
        ]);

        if (! $this->kioskPinManager->isVerified($request)) {
            return $next($request);
        }

        $identifier = $this->kioskDeviceResolver->identifierFromRequest($request);

        if ($identifier === null) {
            $request->attributes->set('kioskDevice', null);

            $allowsMissingDeviceForDevelopment = (bool) config(
                'app.allow_kiosk_without_device_headers',
                app()->environment(['local', 'testing']),
            );

            if (! $allowsMissingDeviceForDevelopment) {
                return $this->inertiaResponse($request, [
                    'step' => 'missing-device',
                    'activeMenu' => 'landing',
                    'pageTitle' => 'Akses Kiosk Ditolak',
                    'pageSubtitle' => 'Perangkat ini belum mengirim identitas kiosk.',
                    'siteName' => $siteSettings['site_name'],
                    'siteTagline' => $siteSettings['site_tagline'],
                    'waitingMessage' => 'Halaman kiosk produksi hanya dapat dibuka dari browser kiosk yang sudah dikelola dan mengirim header X-Kiosk-ID.',
                    'waitingRefreshSeconds' => max((int) $kioskSettings['waiting_refresh_seconds'], 5),
                    'successRedirectSeconds' => max((int) $kioskSettings['success_redirect_seconds'], 3),
                    'device' => null,
                ], 428);
            }

            return $next($request);
        }

        ['device' => $device, 'token' => $token] = $this->kioskDeviceResolver->resolveOrCreate($request);

        if (! $device->isApproved()) {
            $response = $this->inertiaResponse($request, [
                'step' => 'waiting-approval',
                'activeMenu' => 'landing',
                'pageTitle' => 'Perangkat menunggu persetujuan',
                'pageSubtitle' => $this->statusMessageFor($device),
                'siteName' => $siteSettings['site_name'],
                'siteTagline' => $siteSettings['site_tagline'],
                'waitingMessage' => $this->statusMessageFor($device),
                'waitingRefreshSeconds' => max((int) $kioskSettings['waiting_refresh_seconds'], 5),
                'successRedirectSeconds' => max((int) $kioskSettings['success_redirect_seconds'], 3),
                'device' => $device,
            ], 423);

            return $response->cookie($this->kioskDeviceResolver->accessCookie($token));
        }

        if (! $device->hasValidToken($request->cookie(KioskDeviceResolver::DEVICE_COOKIE))) {
            $this->kioskDeviceResolver->forgetAccessCookie();

            return $this->inertiaResponse($request, [
                'step' => 'waiting-approval',
                'activeMenu' => 'landing',
                'pageTitle' => 'Perangkat menunggu persetujuan',
                'pageSubtitle' => 'Perangkat ini perlu disetujui ulang sebelum dapat digunakan lagi.',
                'siteName' => $siteSettings['site_name'],
                'siteTagline' => $siteSettings['site_tagline'],
                'waitingMessage' => 'Perangkat ini perlu disetujui ulang sebelum dapat digunakan lagi.',
                'waitingRefreshSeconds' => max((int) $kioskSettings['waiting_refresh_seconds'], 5),
                'successRedirectSeconds' => max((int) $kioskSettings['success_redirect_seconds'], 3),
                'device' => $device,
            ], 423);
        }

        $request->attributes->set('kioskDevice', $device);

        return $next($request);
    }

    protected function statusMessageFor(KioskDevice $device): string
    {
        return match ($device->status) {
            KioskDevice::STATUS_REJECTED => 'Permintaan perangkat ini ditolak. Ajukan ulang setelah diperiksa super admin.',
            KioskDevice::STATUS_REVOKED => 'Akses perangkat ini telah dicabut. Minta persetujuan ulang dari super admin.',
            default => 'Perangkat ini sedang menunggu persetujuan super admin.',
        };
    }

    /**
     * @param  array<string, mixed>  $props
     */
    protected function inertiaResponse(Request $request, array $props, int $status): Response
    {
        return Inertia::render('Kiosk/Index', $props)
            ->toResponse($request)
            ->setStatusCode($status);
    }
}
