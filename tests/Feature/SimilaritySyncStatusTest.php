<?php

use App\Jobs\SyncSkripsiToSimilarity;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use App\Services\SimilarityApiService;
use App\Services\SimilaritySyncStatusService;
use Illuminate\Support\Facades\Queue;

it('sync skripsi job marks status as synced after successful api upsert', function () {
    Queue::fake();

    $skripsi = Skripsi::factory()->create();
    $statusService = app(SimilaritySyncStatusService::class);

    $statusService->markQueued($skripsi);

    $api = Mockery::mock(SimilarityApiService::class);
    $api->shouldReceive('upsert')
        ->once()
        ->andReturn(true);

    $job = new SyncSkripsiToSimilarity($skripsi->id);
    $job->handle($api, $statusService);

    $status = SimilaritySyncStatus::query()
        ->where('source_skripsi_id', $skripsi->id)
        ->firstOrFail();

    expect($status->status)->toBe(SimilaritySyncStatus::STATUS_SYNCED)
        ->and($status->attempts)->toBe(1)
        ->and($status->last_synced_at)->not->toBeNull()
        ->and($status->last_error)->toBeNull();
});

it('sync skripsi job marks status as failed after api error', function () {
    Queue::fake();

    $skripsi = Skripsi::factory()->create();
    $statusService = app(SimilaritySyncStatusService::class);

    $statusService->markQueued($skripsi);

    $api = Mockery::mock(SimilarityApiService::class);
    $api->shouldReceive('upsert')
        ->once()
        ->andReturn(false);

    $job = new SyncSkripsiToSimilarity($skripsi->id);

    try {
        $job->handle($api, $statusService);
    } catch (RuntimeException $exception) {
        $job->failed($exception);
    }

    $status = SimilaritySyncStatus::query()
        ->where('source_skripsi_id', $skripsi->id)
        ->firstOrFail();

    expect($status->status)->toBe(SimilaritySyncStatus::STATUS_FAILED)
        ->and($status->attempts)->toBe(1)
        ->and($status->last_error)->toContain('Gagal menyinkronkan skripsi');
});
