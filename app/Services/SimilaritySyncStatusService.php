<?php

namespace App\Services;

use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;

class SimilaritySyncStatusService
{
    public function markQueued(Skripsi $skripsi, string $operation = SimilaritySyncStatus::OPERATION_UPSERT): SimilaritySyncStatus
    {
        return SimilaritySyncStatus::query()->updateOrCreate(
            ['source_skripsi_id' => $skripsi->id],
            [
                'status' => SimilaritySyncStatus::STATUS_PENDING,
                'last_operation' => $operation,
                'last_error' => null,
            ],
        );
    }

    public function markProcessing(int $skripsiId, string $operation = SimilaritySyncStatus::OPERATION_UPSERT): SimilaritySyncStatus
    {
        $status = SimilaritySyncStatus::query()->firstOrNew([
            'source_skripsi_id' => $skripsiId,
        ]);

        $status->fill([
            'status' => SimilaritySyncStatus::STATUS_SYNCING,
            'last_operation' => $operation,
            'last_attempt_at' => now(),
            'last_error' => null,
            'attempts' => ($status->attempts ?? 0) + 1,
        ]);

        $status->save();

        return $status;
    }

    public function markSynced(int $skripsiId, string $operation = SimilaritySyncStatus::OPERATION_UPSERT): SimilaritySyncStatus
    {
        $status = SimilaritySyncStatus::query()->firstOrNew([
            'source_skripsi_id' => $skripsiId,
        ]);

        $status->fill([
            'status' => SimilaritySyncStatus::STATUS_SYNCED,
            'last_operation' => $operation,
            'last_synced_at' => now(),
            'last_error' => null,
        ]);

        $status->save();

        return $status;
    }

    public function markFailed(
        int $skripsiId,
        string $errorMessage,
        string $operation = SimilaritySyncStatus::OPERATION_UPSERT,
    ): SimilaritySyncStatus {
        $status = SimilaritySyncStatus::query()->firstOrNew([
            'source_skripsi_id' => $skripsiId,
        ]);

        $status->fill([
            'status' => SimilaritySyncStatus::STATUS_FAILED,
            'last_operation' => $operation,
            'last_attempt_at' => $status->last_attempt_at ?? now(),
            'last_error' => mb_substr($errorMessage, 0, 2000),
        ]);

        $status->save();

        return $status;
    }

    public function markAllQueuedForFullSync(): void
    {
        $timestamp = now();

        SimilaritySyncStatus::query()->update([
            'status' => SimilaritySyncStatus::STATUS_PENDING,
            'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
            'last_synced_at' => null,
            'last_error' => null,
            'updated_at' => $timestamp,
        ]);

        Skripsi::query()
            ->select('id')
            ->chunkById(500, function ($skripsis) use ($timestamp): void {
                $rows = $skripsis->map(fn (Skripsi $skripsi): array => [
                    'source_skripsi_id' => $skripsi->id,
                    'status' => SimilaritySyncStatus::STATUS_PENDING,
                    'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
                    'attempts' => 0,
                    'last_attempt_at' => null,
                    'last_synced_at' => null,
                    'last_error' => null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ])->all();

                SimilaritySyncStatus::query()->upsert(
                    $rows,
                    ['source_skripsi_id'],
                    ['status', 'last_operation', 'last_synced_at', 'last_error', 'updated_at'],
                );
            });
    }
}
