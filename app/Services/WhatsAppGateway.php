<?php

namespace App\Services;

use App\Repositories\SettingRepository;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
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
        if (! $this->configured()) {
            throw new RuntimeException('Gateway WhatsApp belum dikonfigurasi.');
        }

        $response = $this->http
            ->acceptJson()
            ->timeout(10)
            ->retry(2, 200, throw: false)
            ->withHeaders([
                'Authorization' => (string) $this->apiToken(),
            ])
            ->asForm()
            ->post($this->apiUrl(), [
                'target' => $phoneNumber,
                'message' => $message,
            ])
            ->throw();

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
        $url = $this->settingRepository->get('integration', 'whatsapp_api_url');

        return is_string($url) && $url !== '' ? $url : null;
    }

    protected function apiToken(): ?string
    {
        $token = $this->settingRepository->get('integration', 'whatsapp_api_token');

        return is_string($token) && $token !== '' ? $token : null;
    }
}
