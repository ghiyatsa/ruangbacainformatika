<?php

namespace App\Services;

use App\Models\Skripsi;

class SimilarityIndexReconciliationService
{
    private const MISSING_MESSAGE = 'Embedding tidak ditemukan di Similarity API saat rekonsiliasi status.';

    public function __construct(
        private readonly SimilarityApiService $api,
        private readonly SimilaritySyncStatusService $statusService,
    ) {}

    /**
     * @return array{indexed_count: int, matched_count: int, missing_count: int, orphan_index_count: int}
     */
    public function reconcile(int $pageSize = 500): array
    {
        $indexedIds = $this->fetchAllIndexedIds($pageSize);
        $knownIndexedIds = $this->filterExistingSkripsiIds($indexedIds);
        $timestamp = now();

        $this->statusService->deleteOrphanStatuses();

        foreach (array_chunk($knownIndexedIds, 500) as $chunk) {
            $this->statusService->markIndexedIdsAsSynced($chunk, $timestamp);
        }

        $missingCount = 0;

        Skripsi::query()
            ->select('id')
            ->when(
                $knownIndexedIds !== [],
                fn ($query) => $query->whereNotIn('id', $knownIndexedIds),
            )
            ->chunkById(500, function ($skripsis) use (&$missingCount, $timestamp): void {
                $ids = $skripsis->pluck('id')->map(static fn ($id): int => (int) $id)->all();

                $missingCount += count($ids);
                $this->statusService->markIdsMissingFromIndex($ids, self::MISSING_MESSAGE, $timestamp);
            });

        return [
            'indexed_count' => count($indexedIds),
            'matched_count' => count($knownIndexedIds),
            'missing_count' => $missingCount,
            'orphan_index_count' => count($indexedIds) - count($knownIndexedIds),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function fetchAllIndexedIds(int $pageSize): array
    {
        $ids = [];
        $offset = 0;

        do {
            $result = $this->api->indexedIds($pageSize, $offset);

            if ($result === null) {
                throw new \RuntimeException('Gagal mengambil daftar skripsi yang sudah terindeks dari Similarity API.');
            }

            $ids = [...$ids, ...$result['ids']];
            $offset = $result['next_offset'] ?? 0;
        } while ($result['next_offset'] !== null);

        $uniqueIds = array_values(array_unique(array_map(static fn (int $id): int => (int) $id, $ids)));
        sort($uniqueIds);

        return $uniqueIds;
    }

    /**
     * @param  array<int, int>  $indexedIds
     * @return array<int, int>
     */
    private function filterExistingSkripsiIds(array $indexedIds): array
    {
        if ($indexedIds === []) {
            return [];
        }

        $knownIndexedIds = [];

        foreach (array_chunk($indexedIds, 500) as $chunk) {
            $knownIndexedIds = [
                ...$knownIndexedIds,
                ...Skripsi::query()
                    ->whereKey($chunk)
                    ->pluck('id')
                    ->map(static fn ($id): int => (int) $id)
                    ->all(),
            ];
        }

        sort($knownIndexedIds);

        return $knownIndexedIds;
    }
}
