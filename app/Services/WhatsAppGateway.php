<?php

namespace App\Services;

use App\Models\WhatsAppMessageLog;
use App\Notifications\Messages\WhatsAppMessage;
use App\Repositories\SettingRepository;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;

class WhatsAppGateway
{
    public function __construct(
        protected SettingRepository $settingRepository,
        protected HttpFactory $http,
    ) {}

    public function configured(): bool
    {
        return filled($this->apiUrl()) && filled($this->apiToken());
    }

    /**
     * Memeriksa status perangkat WhatsApp gateway (Fonnte).
     *
     * Hasil disimpan singkat di cache agar dashboard tidak
     * memanggil API Fonnte berulang kali pada setiap render.
     *
     * @return array<string, mixed>
     */
    public function deviceStatus(bool $refresh = false): array
    {
        if (! $this->configured()) {
            return [
                'configured' => false,
                'connected' => false,
                'reason' => 'Gateway WhatsApp belum dikonfigurasi.',
                'checked_at' => now()->toIso8601String(),
            ];
        }

        $cacheKey = 'whatsapp-gateway:device-status';

        if (! $refresh && ! app()->runningUnitTests()) {
            $cached = Cache::get($cacheKey);

            if (is_array($cached)) {
                return $cached;
            }
        }

        try {
            $response = $this->http
                ->acceptJson()
                ->timeout(10)
                ->withHeaders([
                    'Authorization' => (string) $this->apiToken(),
                ])
                ->post($this->deviceUrl());

            $payload = $this->responsePayload($response);
            $status = $payload['status'] ?? null;
            $deviceStatus = $payload['device_status'] ?? null;

            $result = [
                'configured' => true,
                'connected' => $status === true && $deviceStatus === 'connect',
                'device_status' => $deviceStatus,
                'device' => $payload['device'] ?? null,
                'name' => $payload['name'] ?? null,
                'reason' => $status === false
                    ? (string) ($payload['reason'] ?? 'Permintaan ditolak oleh gateway.')
                    : null,
                'checked_at' => now()->toIso8601String(),
            ];
        } catch (\Throwable $exception) {
            $result = [
                'configured' => true,
                'connected' => false,
                'device_status' => null,
                'device' => null,
                'name' => null,
                'reason' => $exception->getMessage(),
                'checked_at' => now()->toIso8601String(),
            ];
        }

        Cache::put($cacheKey, $result, now()->addSeconds(60));

        return $result;
    }

    /**
     * URL endpoint pemeriksaan perangkat, diturunkan dari endpoint kirim.
     */
    public function deviceUrl(): string
    {
        $configured = config('services.fonnte.device_url');

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        $base = rtrim(Str::beforeLast((string) $this->apiUrl(), '/'), '/');

        return $base.'/device';
    }

    public function send(string $phoneNumber, string $message): Response
    {
        return $this->sendMessage(
            $phoneNumber,
            new WhatsAppMessage($message),
        );
    }

    public function sendMessage(string $phoneNumber, WhatsAppMessage $message, ?WhatsAppMessageLog $log = null): Response
    {
        try {
            if (! $this->configured()) {
                throw new RuntimeException('Gateway WhatsApp belum dikonfigurasi.');
            }

            $this->ensureRoutineDeliveryIsHealthy($message);

            $response = $this->sendWithPacing(function () use ($message, $phoneNumber): Response {
                return $this->http
                    ->acceptJson()
                    ->timeout(10)
                    ->retry(2, 200, throw: false)
                    ->withHeaders([
                        'Authorization' => (string) $this->apiToken(),
                    ])
                    ->asForm()
                    ->post($this->apiUrl(), [
                        'target' => $phoneNumber,
                        'message' => $message->content,
                        'connectOnly' => true,
                    ])
                    ->throw();
            }, $message);

            $payload = $this->responsePayload($response);
            $status = $payload['status'] ?? $payload['Status'] ?? null;

            if ($status === false) {
                $reason = $payload['reason'] ?? $payload['detail'] ?? 'Pengiriman WhatsApp ditolak oleh gateway.';
                $log?->markFailed((string) $reason, $payload);

                throw new RuntimeException((string) $reason);
            }

            $log?->markSent($payload);

            return $response;
        } catch (\Throwable $exception) {
            if ($log?->status !== WhatsAppMessageLog::StatusFailed) {
                $log?->markFailed($exception->getMessage());
            }

            throw $exception;
        }
    }

    protected function apiUrl(): ?string
    {
        $url = $this->settingRepository->get(
            'integration',
            'whatsapp_api_url',
            config('services.fonnte.url'),
        );

        return is_string($url) && $url !== '' ? $url : null;
    }

    protected function apiToken(): ?string
    {
        $token = $this->settingRepository->get(
            'integration',
            'whatsapp_api_token',
            config('services.fonnte.token'),
        );

        if (! is_string($token) || $token === '') {
            return null;
        }

        try {
            $token = decrypt($token);
        } catch (\Exception) {
        }

        return $token !== '' ? $token : null;
    }

    protected function ensureRoutineDeliveryIsHealthy(WhatsAppMessage $message): void
    {
        if ($message->bypassPacing) {
            return;
        }

        $failureThreshold = max((int) $this->settingRepository->get(
            'integration',
            'whatsapp_failure_pause_threshold',
            config('services.fonnte.failure_pause_threshold', 5),
        ), 0);

        if ($failureThreshold === 0) {
            return;
        }

        $windowMinutes = max((int) $this->settingRepository->get(
            'integration',
            'whatsapp_failure_pause_window_minutes',
            config('services.fonnte.failure_pause_window_minutes', 15),
        ), 1);

        $recentFailures = WhatsAppMessageLog::query()
            ->where('status', WhatsAppMessageLog::StatusFailed)
            ->where('created_at', '>=', now()->subMinutes($windowMinutes))
            ->count();

        if ($recentFailures >= $failureThreshold) {
            throw new RuntimeException('Pengiriman WhatsApp rutin dijeda sementara karena banyak kegagalan terbaru.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function responsePayload(Response $response): array
    {
        $payload = $response->json();

        return is_array($payload) ? $payload : [];
    }

    /**
     * @param  callable(): Response  $callback
     */
    protected function sendWithPacing(callable $callback, WhatsAppMessage $message): Response
    {
        $isOtp = $message->category === 'otp';

        $intervalSeconds = $isOtp
            ? max((int) config('services.fonnte.otp_send_interval_seconds', 5), 0)
            : max((int) config('services.fonnte.send_interval_seconds', 15), 0);

        if ($message->bypassPacing || $intervalSeconds === 0 || app()->runningUnitTests()) {
            return $callback();
        }

        $lockKey = $isOtp ? 'whatsapp-gateway:otp-send-lock' : 'whatsapp-gateway:send-lock';
        $lastSentKey = $isOtp ? 'whatsapp-gateway:otp-last-sent-at' : 'whatsapp-gateway:last-sent-at';
        $lockSeconds = max($intervalSeconds * 2, 10);

        try {
            return Cache::lock($lockKey, $lockSeconds)->block($lockSeconds, function () use ($callback, $intervalSeconds, $lastSentKey): Response {
                $lastSentAt = Cache::get($lastSentKey);

                if (is_numeric($lastSentAt)) {
                    $remainingDelay = $intervalSeconds - (now()->timestamp - (int) $lastSentAt);

                    if ($remainingDelay > 0) {
                        sleep($remainingDelay);
                    }
                }

                $response = $callback();

                Cache::put($lastSentKey, now()->timestamp, now()->addDay());

                return $response;
            });
        } catch (LockTimeoutException) {
            throw new RuntimeException('Antrean pengiriman WhatsApp sedang penuh. Coba beberapa saat lagi.');
        }
    }
}
