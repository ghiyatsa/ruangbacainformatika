<?php

use App\Jobs\SyncSkripsiToSimilarity;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use App\Services\SimilarityApiService;
use App\Services\SimilaritySyncStatusService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Queue;

it('sync skripsi job marks status as synced after successful api upsert', function () {
    Queue::fake();

    $skripsi = Skripsi::withoutEvents(fn (): Skripsi => Skripsi::factory()->create());
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

    $skripsi = Skripsi::withoutEvents(fn (): Skripsi => Skripsi::factory()->create());
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

it('can reset all similarity statuses to pending for a full resync', function () {
    $skripsis = Skripsi::withoutEvents(fn (): Collection => Skripsi::factory()->count(3)->create());
    $statusService = app(SimilaritySyncStatusService::class);

    SimilaritySyncStatus::query()->updateOrCreate(
        ['source_skripsi_id' => $skripsis[0]->id],
        [
            'status' => SimilaritySyncStatus::STATUS_SYNCED,
            'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
            'attempts' => 4,
            'last_synced_at' => now(),
        ],
    );

    SimilaritySyncStatus::query()->updateOrCreate(
        ['source_skripsi_id' => $skripsis[1]->id],
        [
            'status' => SimilaritySyncStatus::STATUS_FAILED,
            'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
            'attempts' => 2,
            'last_attempt_at' => now(),
            'last_error' => 'API timeout',
        ],
    );

    $statusService->markAllQueuedForFullSync();

    expect(SimilaritySyncStatus::query()->count())->toBe(3)
        ->and(SimilaritySyncStatus::query()->where('status', SimilaritySyncStatus::STATUS_PENDING)->count())->toBe(3)
        ->and(SimilaritySyncStatus::query()->whereNotNull('last_synced_at')->count())->toBe(0)
        ->and(SimilaritySyncStatus::query()->whereNotNull('last_error')->count())->toBe(0);
});

it('removes orphan similarity statuses during a full resync reset', function () {
    $skripsi = Skripsi::withoutEvents(fn (): Skripsi => Skripsi::factory()->create());
    $statusService = app(SimilaritySyncStatusService::class);

    SimilaritySyncStatus::query()->create([
        'source_skripsi_id' => $skripsi->id,
        'status' => SimilaritySyncStatus::STATUS_FAILED,
        'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
        'attempts' => 2,
        'last_error' => 'Masih terkait',
    ]);

    SimilaritySyncStatus::query()->create([
        'source_skripsi_id' => 999999,
        'status' => SimilaritySyncStatus::STATUS_FAILED,
        'last_operation' => SimilaritySyncStatus::OPERATION_DELETE,
        'attempts' => 1,
        'last_error' => 'Yatim',
    ]);

    $statusService->markAllQueuedForFullSync();

    expect(SimilaritySyncStatus::query()->pluck('source_skripsi_id')->all())->toBe([$skripsi->id])
        ->and(SimilaritySyncStatus::query()->value('status'))->toBe(SimilaritySyncStatus::STATUS_PENDING);
});
