<?php

namespace App\Services;

use App\Models\InternshipReport;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SimilaritySyncStatusService
{
    public function deleteStatus(Model|int $model, ?string $type = null): void
    {
        $id = $model instanceof Model ? $model->getKey() : $model;
        $type = $model instanceof Model ? $model->getMorphClass() : ($type ?? Skripsi::class);

        SimilaritySyncStatus::query()
            ->where('syncable_type', $type)
            ->where('syncable_id', $id)
            ->delete();
    }

    public function markQueued(Model $model, string $operation = SimilaritySyncStatus::OPERATION_UPSERT): SimilaritySyncStatus
    {
        return SimilaritySyncStatus::query()->updateOrCreate(
            [
                'syncable_type' => $model->getMorphClass(),
                'syncable_id' => $model->getKey(),
            ],
            [
                'status' => SimilaritySyncStatus::STATUS_PENDING,
                'last_operation' => $operation,
                'last_error' => null,
            ],
        );
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function markQueuedMultiple(array $ids, string $operation = SimilaritySyncStatus::OPERATION_UPSERT, string $type = Skripsi::class): void
    {
        if ($ids === []) {
            return;
        }

        $timestamp = now();

        $rows = collect($ids)
            ->map(fn (int $id): array => [
                'syncable_type' => $type,
                'syncable_id' => $id,
                'status' => SimilaritySyncStatus::STATUS_PENDING,
                'last_operation' => $operation,
                'attempts' => 0,
                'last_attempt_at' => null,
                'last_synced_at' => null,
                'last_error' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ])
            ->all();

        SimilaritySyncStatus::query()->upsert(
            $rows,
            ['syncable_type', 'syncable_id'],
            ['status', 'last_operation', 'last_synced_at', 'last_error', 'updated_at'],
        );
    }

    public function markProcessing(Model|int $model, string $operation = SimilaritySyncStatus::OPERATION_UPSERT, ?string $type = null): SimilaritySyncStatus
    {
        $id = $model instanceof Model ? $model->getKey() : $model;
        $type = $model instanceof Model ? $model->getMorphClass() : ($type ?? Skripsi::class);

        $status = SimilaritySyncStatus::query()->firstOrNew([
            'syncable_type' => $type,
            'syncable_id' => $id,
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

    public function markSynced(Model|int $model, string $operation = SimilaritySyncStatus::OPERATION_UPSERT, ?string $type = null): SimilaritySyncStatus
    {
        $id = $model instanceof Model ? $model->getKey() : $model;
        $type = $model instanceof Model ? $model->getMorphClass() : ($type ?? Skripsi::class);

        $status = SimilaritySyncStatus::query()->firstOrNew([
            'syncable_type' => $type,
            'syncable_id' => $id,
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
        Model|int $model,
        string $errorMessage,
        string $operation = SimilaritySyncStatus::OPERATION_UPSERT,
        ?string $type = null,
    ): SimilaritySyncStatus {
        $id = $model instanceof Model ? $model->getKey() : $model;
        $type = $model instanceof Model ? $model->getMorphClass() : ($type ?? Skripsi::class);

        $status = SimilaritySyncStatus::query()->firstOrNew([
            'syncable_type' => $type,
            'syncable_id' => $id,
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

        $this->deleteOrphanStatuses();

        SimilaritySyncStatus::query()
            ->forExistingRecords()
            ->update([
                'status' => SimilaritySyncStatus::STATUS_PENDING,
                'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
                'last_synced_at' => null,
                'last_error' => null,
                'updated_at' => $timestamp,
            ]);

        // Sync Skripsi
        Skripsi::query()
            ->select('id')
            ->chunkById(500, function ($skripsis) use ($timestamp): void {
                $rows = $skripsis->map(fn (Skripsi $skripsi): array => [
                    'syncable_type' => Skripsi::class,
                    'syncable_id' => $skripsi->id,
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
                    ['syncable_type', 'syncable_id'],
                    ['status', 'last_operation', 'last_synced_at', 'last_error', 'updated_at'],
                );
            });

        // Sync InternshipReport
        InternshipReport::query()
            ->select('id')
            ->chunkById(500, function ($internships) use ($timestamp): void {
                $rows = $internships->map(fn (InternshipReport $internship): array => [
                    'syncable_type' => InternshipReport::class,
                    'syncable_id' => $internship->id,
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
                    ['syncable_type', 'syncable_id'],
                    ['status', 'last_operation', 'last_synced_at', 'last_error', 'updated_at'],
                );
            });
    }

    public function deleteOrphanStatuses(): void
    {
        SimilaritySyncStatus::query()
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('syncable_type', Skripsi::class)
                        ->whereNotExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('skripsis')
                                ->whereColumn('skripsis.id', 'similarity_sync_statuses.syncable_id');
                        });
                })->orWhere(function ($q) {
                    $q->where('syncable_type', InternshipReport::class)
                        ->whereNotExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('internship_reports')
                                ->whereColumn('internship_reports.id', 'similarity_sync_statuses.syncable_id');
                        });
                })->orWhereNotIn('syncable_type', [Skripsi::class, InternshipReport::class]);
            })
            ->delete();
    }

    /**
     * @param  array<int, int>  $skripsiIds
     */
    public function markIndexedIdsAsSynced(array $skripsiIds, CarbonInterface $syncedAt, string $type = Skripsi::class): void
    {
        if ($skripsiIds === []) {
            return;
        }

        SimilaritySyncStatus::query()
            ->where('syncable_type', $type)
            ->whereIn('syncable_id', $skripsiIds)
            ->update([
                'status' => SimilaritySyncStatus::STATUS_SYNCED,
                'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
                'last_synced_at' => $syncedAt,
                'last_error' => null,
                'updated_at' => $syncedAt,
            ]);

        $rows = collect($skripsiIds)
            ->map(fn (int $id): array => [
                'syncable_type' => $type,
                'syncable_id' => $id,
                'status' => SimilaritySyncStatus::STATUS_SYNCED,
                'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
                'attempts' => 0,
                'last_attempt_at' => null,
                'last_synced_at' => $syncedAt,
                'last_error' => null,
                'created_at' => $syncedAt,
                'updated_at' => $syncedAt,
            ])
            ->all();

        SimilaritySyncStatus::query()->upsert(
            $rows,
            ['syncable_type', 'syncable_id'],
            ['status', 'last_operation', 'last_synced_at', 'last_error', 'updated_at'],
        );
    }

    /**
     * @param  array<int, int>  $skripsiIds
     */
    public function markIdsMissingFromIndex(array $skripsiIds, string $errorMessage, CarbonInterface $timestamp, string $type = Skripsi::class): void
    {
        if ($skripsiIds === []) {
            return;
        }

        $errorMessage = mb_substr($errorMessage, 0, 2000);

        SimilaritySyncStatus::query()
            ->where('syncable_type', $type)
            ->whereIn('syncable_id', $skripsiIds)
            ->update([
                'status' => SimilaritySyncStatus::STATUS_FAILED,
                'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
                'last_attempt_at' => $timestamp,
                'last_synced_at' => null,
                'last_error' => $errorMessage,
                'updated_at' => $timestamp,
            ]);

        $rows = collect($skripsiIds)
            ->map(fn (int $id): array => [
                'syncable_type' => $type,
                'syncable_id' => $id,
                'status' => SimilaritySyncStatus::STATUS_FAILED,
                'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
                'attempts' => 0,
                'last_attempt_at' => $timestamp,
                'last_synced_at' => null,
                'last_error' => $errorMessage,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ])
            ->all();

        SimilaritySyncStatus::query()->upsert(
            $rows,
            ['syncable_type', 'syncable_id'],
            ['status', 'last_operation', 'last_attempt_at', 'last_synced_at', 'last_error', 'updated_at'],
        );
    }
}
