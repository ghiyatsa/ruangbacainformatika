<?php

use App\Jobs\SyncSkripsiChunkToSimilarity;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use App\Models\User;
use App\Services\SimilarityApiService;
use App\Services\SimilarityFullSyncDispatcher;
use App\Services\SimilaritySyncStatusService;
use Illuminate\Bus\PendingBatch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;

it('similarity full sync dispatcher queues the sync command when not running synchronously', function () {
    app()['env'] = 'production';
    config()->set('queue.default', 'database');
    Bus::fake();

    $skripsis = Skripsi::withoutEvents(fn (): Collection => Skripsi::factory()->count(3)->create());
    $user = User::factory()->create();

    SimilaritySyncStatus::query()->updateOrCreate(
        ['source_skripsi_id' => $skripsis[0]->id],
        [
            'status' => SimilaritySyncStatus::STATUS_SYNCED,
            'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
            'attempts' => 2,
            'last_synced_at' => now(),
            'last_error' => null,
        ],
    );
    SimilaritySyncStatus::query()->updateOrCreate(
        ['source_skripsi_id' => $skripsis[1]->id],
        [
            'status' => SimilaritySyncStatus::STATUS_FAILED,
            'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
            'attempts' => 1,
            'last_attempt_at' => now(),
            'last_error' => 'Timeout',
        ],
    );

    $result = app(SimilarityFullSyncDispatcher::class)->dispatch(chunk: 2, initiatedByUserId: $user->id);

    Bus::assertBatched(function (PendingBatch $batch): bool {
        return $batch->name === 'similarity-full-sync'
            && $batch->jobs->count() === 2
            && $batch->jobs[0] instanceof SyncSkripsiChunkToSimilarity
            && $batch->jobs[1] instanceof SyncSkripsiChunkToSimilarity
            && $batch->jobs[0]->resetIndex === true
            && $batch->jobs[1]->resetIndex === false
            && count($batch->jobs[0]->skripsiIds) === 2
            && count($batch->jobs[1]->skripsiIds) === 1;
    });

    expect($result['mode'])->toBe('queued')
        ->and($result['success'])->toBeTrue()
        ->and($result['error_message'])->toBeNull()
        ->and($result['batch_id'])->not->toBeNull()
        ->and(SimilaritySyncStatus::query()->count())->toBe(3)
        ->and(SimilaritySyncStatus::query()->where('status', SimilaritySyncStatus::STATUS_PENDING)->count())->toBe(3)
        ->and(SimilaritySyncStatus::query()->whereNotNull('last_synced_at')->count())->toBe(0)
        ->and(SimilaritySyncStatus::query()->whereNotNull('last_error')->count())->toBe(0);
});

it('similarity full sync dispatcher can force a synchronous full rebuild', function () {
    Artisan::shouldReceive('call')
        ->once()
        ->with('skripsi:sync --chunk=100 --reset')
        ->andReturn(0);

    $skripsi = Skripsi::withoutEvents(fn (): Skripsi => Skripsi::factory()->create());

    SimilaritySyncStatus::query()->updateOrCreate(
        ['source_skripsi_id' => $skripsi->id],
        [
            'status' => SimilaritySyncStatus::STATUS_FAILED,
            'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
            'attempts' => 1,
            'last_error' => 'Timeout',
        ],
    );

    $result = app(SimilarityFullSyncDispatcher::class)->dispatch(forceSync: true);

    expect($result['mode'])->toBe('sync')
        ->and($result['success'])->toBeTrue()
        ->and($result['error_message'])->toBeNull()
        ->and($result['batch_id'])->toBeNull()
        ->and(SimilaritySyncStatus::query()->where('status', SimilaritySyncStatus::STATUS_PENDING)->count())->toBe(1);
});

it('similarity full sync dispatcher returns a safe failure result when reset preparation throws', function () {
    $dispatcher = new SimilarityFullSyncDispatcher(new class extends SimilaritySyncStatusService
    {
        public function markAllQueuedForFullSync(): void
        {
            throw new RuntimeException('Forced dispatcher failure');
        }
    });

    $result = $dispatcher->dispatch();

    expect($result['mode'])->toBe('queued')
        ->and($result['success'])->toBeFalse()
        ->and($result['error_message'])->toBe('Forced dispatcher failure')
        ->and($result['batch_id'])->toBeNull();
});

it('similarity chunk job marks skripsi as synced after a successful bulk upsert', function () {
    $skripsis = Skripsi::withoutEvents(fn (): Collection => Skripsi::factory()->count(2)->create());

    $api = mock(SimilarityApiService::class);
    $api->shouldReceive('bulkUpsert')
        ->once()
        ->withArgs(function (array $items, bool $resetIndex) use ($skripsis): bool {
            return $resetIndex
                && count($items) === 2
                && $items[0]['skripsi_id'] === $skripsis[0]->id
                && $items[1]['skripsi_id'] === $skripsis[1]->id;
        })
        ->andReturnTrue();

    $job = new SyncSkripsiChunkToSimilarity($skripsis->pluck('id')->all(), true);
    $job->handle($api, app(SimilaritySyncStatusService::class));

    expect(SimilaritySyncStatus::query()->where('status', SimilaritySyncStatus::STATUS_SYNCED)->count())->toBe(2)
        ->and(SimilaritySyncStatus::query()->where('status', SimilaritySyncStatus::STATUS_FAILED)->count())->toBe(0);
});

it('similarity chunk job marks skripsi as failed when bulk upsert fails', function () {
    $skripsis = Skripsi::withoutEvents(fn (): Collection => Skripsi::factory()->count(2)->create());

    $api = mock(SimilarityApiService::class);
    $api->shouldReceive('bulkUpsert')
        ->once()
        ->andReturnFalse();

    $job = new SyncSkripsiChunkToSimilarity($skripsis->pluck('id')->all());

    expect(fn () => $job->handle($api, app(SimilaritySyncStatusService::class)))
        ->toThrow(RuntimeException::class, 'Bulk sinkronisasi ke Similarity API gagal.');

    expect(SimilaritySyncStatus::query()->where('status', SimilaritySyncStatus::STATUS_FAILED)->count())->toBe(2);
});
