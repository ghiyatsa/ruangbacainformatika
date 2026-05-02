<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SimilarityApiService
{
    private string $baseUrl;

    private string $secret;

    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.similarity_api.url', 'http://localhost:8181'), '/');
        $this->secret = config('services.similarity_api.secret', 'changeme-secret-token');
        $this->timeout = (int) config('services.similarity_api.timeout', 10);
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withToken($this->secret)
            ->acceptJson();
    }

    /**
     * Cek kemiripan judul dengan seluruh database.
     *
     * @param  int  $topK  Jumlah hasil teratas (default 5)
     * @param  float  $threshold  Skor minimum (default 0.5)
     */
    public function checkSimilarity(
        string $judul,
        int $topK = 5,
        float $threshold = 0.5,
    ): array {
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

        return ['total_found' => 0, 'results' => [], 'peringatan' => null];
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
            return Http::baseUrl($this->baseUrl)
                ->timeout(5)
                ->get('/health')
                ->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
