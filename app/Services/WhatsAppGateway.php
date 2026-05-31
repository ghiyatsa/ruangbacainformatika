<?php

namespace App\Services;

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

    public function sendMessage(string $phoneNumber, WhatsAppMessage $message): Response
    {
        if (! $this->configured()) {
            throw new RuntimeException('Gateway WhatsApp belum dikonfigurasi.');
        }

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
                ])
                ->throw();
        }, $message->bypassPacing);

        $payload = $response->json();
        $status = is_array($payload)
            ? ($payload['status'] ?? $payload['Status'] ?? null)
            : null;

        if ($status === false) {
            $reason = is_array($payload)
                ? ($payload['reason'] ?? $payload['detail'] ?? 'Pengiriman WhatsApp ditolak oleh gateway.')
                : 'Pengiriman WhatsApp ditolak oleh gateway.';

            throw new RuntimeException((string) $reason);
        }

        return $response;
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
