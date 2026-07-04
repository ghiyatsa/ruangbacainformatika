<?php

namespace App\Services;

use App\Repositories\SettingRepository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SimilarityApiService
{
    /**
     * @var array<int, int>
     */
    private const RETRY_DELAYS_MS = [200, 500, 1000];

    private const BULK_JOB_MAX_POLLS = 120;

    private const BULK_JOB_POLL_DELAY_US = 500000;

    private ?string $baseUrl = null;

    private ?string $secret = null;

    private ?int $timeout = null;

    private ?int $topK = null;

    private ?float $threshold = null;

    private ?float $weightJudul = null;

    private ?float $weightAbstrak = null;

    private ?float $weightKataKunci = null;

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

    private function getTitleWeight(): float
    {
        return $this->weightJudul ??= (float) $this->settings->get('integration', 'similarity_weight_judul', 0.7);
    }

    private function getAbstractWeight(): float
    {
        return $this->weightAbstrak ??= (float) $this->settings->get('integration', 'similarity_weight_abstrak', 0.2);
    }

    private function getKeywordWeight(): float
    {
        return $this->weightKataKunci ??= (float) $this->settings->get('integration', 'similarity_weight_kata_kunci', 0.1);
    }

    /**
     * @return array<string, float>
     */
    private function getWeights(): array
    {
        return [
            'bobot_judul' => $this->getTitleWeight(),
            'bobot_abstrak' => $this->getAbstractWeight(),
            'bobot_kata_kunci' => $this->getKeywordWeight(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withConfiguredWeights(array $data): array
    {
        return $data + $this->getWeights();
    }

    private function getHfToken(): ?string
    {
        return $this->hfToken ??= config('services.huggingface.token');
    }

    private function client(): PendingRequest
    {
        $request = Http::baseUrl($this->getBaseUrl())
            ->connectTimeout(3)
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

    private function shouldRetryStatus(int $status): bool
    {
        return in_array($status, [429, 500, 502, 503, 504], true);
    }

    private function logFailedResponse(string $operation, Response $response): void
    {
        Log::warning("Similarity API: {$operation} gagal", [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
    }

    private function waitForBulkJob(string $jobId): bool
    {
        for ($poll = 0; $poll < self::BULK_JOB_MAX_POLLS; $poll++) {
            $response = $this->client()->get("/api/v1/sync/jobs/{$jobId}");

            if (! $response->successful()) {
                $this->logFailedResponse('bulk-upsert status', $response);

                return false;
            }

            $payload = $response->json();
            $status = $payload['status'] ?? null;

            if ($status === 'completed') {
                return true;
            }

            if ($status === 'failed') {
                Log::warning('Similarity API: bulk-upsert job gagal', [
                    'job_id' => $jobId,
                    'payload' => $payload,
                ]);

                return false;
            }

            usleep(self::BULK_JOB_POLL_DELAY_US);
        }

        Log::warning('Similarity API: bulk-upsert job timeout', [
            'job_id' => $jobId,
        ]);

        return false;
    }

    /**
     * @param  callable(PendingRequest): Response  $callback
     */
    private function sendWithRetry(callable $callback): Response
    {
        $maxRetries = count(self::RETRY_DELAYS_MS);

        for ($attempt = 0; ; $attempt++) {
            try {
                $response = $callback($this->client());

                if (! $this->shouldRetryStatus($response->status()) || $attempt >= $maxRetries) {
                    return $response;
                }
            } catch (ConnectionException $exception) {
                if ($attempt >= $maxRetries) {
                    throw $exception;
                }
            }

            usleep(self::RETRY_DELAYS_MS[$attempt] * 1000);
        }
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
        ?string $documentType = null,
    ): ?array {
        $topK ??= $this->getTopK();
        $threshold ??= $this->getThreshold();

        try {
            $response = $this->sendWithRetry(fn (PendingRequest $request): Response => $request->post('/api/v1/similarity/check', [
                'judul' => $judul,
                'top_k' => $topK,
                'threshold' => $threshold,
                'document_type' => $documentType,
            ]));

            if ($response->successful()) {
                return $response->json();
            }

            $this->logFailedResponse('check', $response);
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
            $response = $this->sendWithRetry(
                fn (PendingRequest $request): Response => $request->post('/api/v1/sync/upsert', $this->withConfiguredWeights($data)),
            );

            if ($response->successful()) {
                return true;
            }

            $this->logFailedResponse('upsert', $response);
        } catch (\Exception $e) {
            Log::error('Similarity API: upsert gagal', ['error' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * Kirim banyak skripsi sekaligus.
     *
     * @param  array  $items  Array of associative arrays (same structure as upsert)
     */
    public function bulkUpsert(array $items, bool $resetIndex = false): bool
    {
        $items = array_map(
            fn (array $item): array => $this->withConfiguredWeights($item),
            $items,
        );

        try {
            $response = $this->sendWithRetry(
                fn (PendingRequest $request): Response => $request->post('/api/v1/sync/bulk-upsert', [
                    'data' => $items,
                    'reset_index' => $resetIndex,
                ]),
            );

            if ($response->accepted()) {
                $jobId = $response->json('job_id');

                if (! is_string($jobId) || blank($jobId)) {
                    Log::warning('Similarity API: bulk-upsert tidak mengembalikan job_id', [
                        'body' => $response->json(),
                    ]);

                    return false;
                }

                return $this->waitForBulkJob($jobId);
            }

            if ($response->successful()) {
                return true;
            }

            $this->logFailedResponse('bulk-upsert', $response);
        } catch (\Exception $e) {
            Log::error('Similarity API: bulk-upsert gagal', ['error' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * @return array{ids: array<int, int>, total_indexed: int, next_offset: int|null}|null
     */
    public function indexedIds(int $limit = 500, int $offset = 0): ?array
    {
        try {
            $response = $this->sendWithRetry(
                fn (PendingRequest $request): Response => $request->get('/api/v1/sync/indexed-ids', [
                    'limit' => $limit,
                    'offset' => $offset,
                ]),
            );

            if ($response->successful()) {
                $ids = array_values(array_map(
                    static fn (mixed $id): int => (int) $id,
                    array_filter(
                        $response->json('ids', []),
                        static fn (mixed $id): bool => is_numeric($id),
                    ),
                ));

                return [
                    'ids' => $ids,
                    'total_indexed' => (int) $response->json('total_indexed', count($ids)),
                    'next_offset' => is_numeric($response->json('next_offset'))
                        ? (int) $response->json('next_offset')
                        : null,
                ];
            }

            $this->logFailedResponse('indexed-ids', $response);
        } catch (\Exception $e) {
            Log::error('Similarity API: indexed-ids gagal', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Hapus skripsi dari vector store berdasarkan ID Laravel.
     */
    public function delete(int $laravelId): bool
    {
        try {
            $response = $this->sendWithRetry(
                fn (PendingRequest $request): Response => $request->delete("/api/v1/sync/{$laravelId}"),
            );

            if ($response->successful() || $response->notFound()) {
                return true;
            }

            $this->logFailedResponse('delete', $response);
        } catch (\Exception $e) {
            Log::error('Similarity API: delete gagal', ['error' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * Cek apakah API sedang berjalan.
     */
    public function isHealthy(): bool
    {
        try {
            return $this->client()
                ->connectTimeout(3)
                ->timeout(5)
                ->get('/health')
                ->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
