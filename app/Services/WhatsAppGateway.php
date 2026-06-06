<?php

namespace App\Services;

use App\Models\WhatsAppMessageLog;
use App\Notifications\Messages\WhatsAppMessage;
use App\Repositories\SettingRepository;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
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
            }, $message->bypassPacing);

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
        if ($message->bypassPacing || $message->category === 'otp') {
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
            ->where('category', '!=', 'otp')
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
    protected function sendWithPacing(callable $callback, bool $bypassPacing = false): Response
    {
        $intervalSeconds = max((int) config('services.fonnte.send_interval_seconds', 15), 0);

        if ($bypassPacing || $intervalSeconds === 0 || app()->runningUnitTests()) {
            return $callback();
        }

        $lockSeconds = max($intervalSeconds * 2, 10);

        try {
            return Cache::lock('whatsapp-gateway:send-lock', $lockSeconds)->block($lockSeconds, function () use ($callback, $intervalSeconds): Response {
                $lastSentAt = Cache::get('whatsapp-gateway:last-sent-at');

                if (is_numeric($lastSentAt)) {
                    $remainingDelay = $intervalSeconds - (now()->timestamp - (int) $lastSentAt);

                    if ($remainingDelay > 0) {
                        sleep($remainingDelay);
                    }
                }

                $response = $callback();

                Cache::put('whatsapp-gateway:last-sent-at', now()->timestamp, now()->addDay());

                return $response;
            });
        } catch (LockTimeoutException) {
            throw new RuntimeException('Antrean pengiriman WhatsApp sedang penuh. Coba beberapa saat lagi.');
        }
    }
}
