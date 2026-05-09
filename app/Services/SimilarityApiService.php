<?php

namespace App\Services;

use App\Repositories\SettingRepository;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SimilarityApiService
{
    private ?string $baseUrl = null;

    private ?string $secret = null;

    private ?int $timeout = null;

    private ?int $topK = null;

    private ?float $threshold = null;

    private ?string $hfToken = null;

    public function __construct(private SettingRepository $settings) {}

    private function getBaseUrl(): string
    {
        return $this->baseUrl ??= rtrim($this->settings->get('integration', 'similarity_api_url', config('services.similarity_api.url', 'http://localhost:8181')), '/');
    }

    private function getSecret(): string
    {
        if ($this->secret === null) {
            $rawSecret = $this->settings->get('integration', 'similarity_api_secret', config('services.similarity_api.secret', 'changeme-secret-token'));
            try {
                $this->secret = filled($rawSecret) ? decrypt($rawSecret) : '';
            } catch (\Exception) {
                $this->secret = (string) $rawSecret;
            }
        }

        return $this->secret;
    }

    private function getTimeout(): int
    {
        return $this->timeout ??= (int) $this->settings->get('integration', 'similarity_api_timeout', config('services.similarity_api.timeout', 10));
    }

    private function getTopK(): int
    {
        return $this->topK ??= (int) $this->settings->get('integration', 'similarity_api_top_k', 5);
    }

    private function getThreshold(): float
    {
        return $this->threshold ??= (float) $this->settings->get('integration', 'similarity_api_threshold', 0.5);
    }

    private function getHfToken(): ?string
    {
        return $this->hfToken ??= config('services.huggingface.token');
    }

    private function client(): PendingRequest
    {
        $request = Http::baseUrl($this->getBaseUrl())
            ->timeout($this->getTimeout())
            ->acceptJson();

        $secret = $this->getSecret();
        $hfToken = $this->getHfToken();

        // If HF token is present, use it for Bearer auth (Hugging Face Private Space requirement)
        // And send the app secret in a custom header
        if ($hfToken) {
            $request->withToken($hfToken)
                ->withHeaders([
                    'X-Similarity-Api-Secret' => $secret,
                ]);
        } else {
            // Default behavior if not using HF Private Space
            $request->withToken($secret);
        }

        return $request;
    }

    /**
     * Cek kemiripan judul dengan seluruh database.
     *
     * @param  int  $topK  Jumlah hasil teratas (default 5)
     * @param  float  $threshold  Skor minimum (default 0.5)
     * @return array|null Null jika terjadi kesalahan API
     */
    public function checkSimilarity(
        string $judul,
        ?int $topK = null,
        ?float $threshold = null,
    ): ?array {
        $topK ??= $this->getTopK();
        $threshold ??= $this->getThreshold();

        try {
            $response = $this->client()->post('/api/v1/similarity/check', [
                'judul' => $judul,
                'top_k' => $topK,
                'threshold' => $threshold,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Similarity API: check gagal', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('Similarity API: connection error', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Kirim satu skripsi ke API (insert atau update).
     */
    public function upsert(array $data): bool
    {
        try {
            return $this->client()
                ->post('/api/v1/sync/upsert', $data)
                ->successful();
        } catch (\Exception $e) {
            Log::error('Similarity API: upsert gagal', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Kirim banyak skripsi sekaligus.
     *
     * @param  array  $items  Array of associative arrays (same structure as upsert)
     */
    public function bulkUpsert(array $items): bool
    {
        try {
            return $this->client()
                ->post('/api/v1/sync/bulk-upsert', ['data' => $items])
                ->successful();
        } catch (\Exception $e) {
            Log::error('Similarity API: bulk-upsert gagal', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Hapus skripsi dari vector store berdasarkan ID Laravel.
     */
    public function delete(int $laravelId): bool
    {
        try {
            return $this->client()
                ->delete("/api/v1/sync/{$laravelId}")
                ->successful();
        } catch (\Exception $e) {
            Log::error('Similarity API: delete gagal', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Cek apakah API sedang berjalan.
     */
    public function isHealthy(): bool
    {
        try {
            return $this->client()
                ->timeout(5)
                ->get('/health')
                ->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
