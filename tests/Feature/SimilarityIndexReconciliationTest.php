<?php

use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use App\Services\SimilarityApiService;
use App\Services\SimilarityIndexReconciliationService;

it('reconciles similarity statuses using indexed skripsi ids from the api', function () {
    $skripsis = Skripsi::withoutEvents(fn () => Skripsi::factory()->count(3)->create());

    SimilaritySyncStatus::query()->create([
        'source_skripsi_id' => $skripsis[0]->id,
        'status' => SimilaritySyncStatus::STATUS_FAILED,
        'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
        'attempts' => 2,
        'last_error' => 'Timeout',
    ]);

    SimilaritySyncStatus::query()->create([
        'source_skripsi_id' => $skripsis[1]->id,
        'status' => SimilaritySyncStatus::STATUS_SYNCING,
        'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
        'attempts' => 1,
        'last_attempt_at' => now(),
    ]);

    $api = mock(SimilarityApiService::class);
    $api->shouldReceive('indexedIds')
        ->once()
        ->with(500, 0)
        ->andReturn([
            'ids' => [$skripsis[0]->id, $skripsis[1]->id],
            'total_indexed' => 2,
            'next_offset' => null,
        ]);

    $summary = app()->make(SimilarityIndexReconciliationService::class, [
        'api' => $api,
    ])->reconcile();

    expect($summary)->toBe([
        'indexed_count' => 2,
        'matched_count' => 2,
        'missing_count' => 1,
        'orphan_index_count' => 0,
    ]);

    expect(
        SimilaritySyncStatus::query()
            ->whereIn('source_skripsi_id', [$skripsis[0]->id, $skripsis[1]->id])
            ->pluck('status', 'source_skripsi_id')
            ->all()
    )->toBe([
        $skripsis[0]->id => SimilaritySyncStatus::STATUS_SYNCED,
        $skripsis[1]->id => SimilaritySyncStatus::STATUS_SYNCED,
    ]);

    $missingStatus = SimilaritySyncStatus::query()
        ->where('source_skripsi_id', $skripsis[2]->id)
        ->firstOrFail();

    expect($missingStatus->status)->toBe(SimilaritySyncStatus::STATUS_FAILED)
        ->and($missingStatus->last_error)->toContain('Embedding tidak ditemukan');
});

it('counts orphan indexed ids that no longer exist in laravel', function () {
    $skripsi = Skripsi::withoutEvents(fn (): Skripsi => Skripsi::factory()->create());

    $api = mock(SimilarityApiService::class);
    $api->shouldReceive('indexedIds')
        ->once()
        ->with(500, 0)
        ->andReturn([
            'ids' => [$skripsi->id, 999999],
            'total_indexed' => 2,
            'next_offset' => null,
        ]);

    $summary = app()->make(SimilarityIndexReconciliationService::class, [
        'api' => $api,
    ])->reconcile();

    expect($summary['matched_count'])->toBe(1)
        ->and($summary['orphan_index_count'])->toBe(1)
        ->and($summary['missing_count'])->toBe(0);
});
