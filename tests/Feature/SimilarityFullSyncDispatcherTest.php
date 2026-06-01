<?php

use App\Jobs\RunFullSimilaritySync;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use App\Services\SimilarityFullSyncDispatcher;
use App\Services\SimilaritySyncStatusService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;

it('similarity full sync dispatcher queues the sync command when not running synchronously', function () {
    app()['env'] = 'production';
    Queue::fake();

    $skripsis = Skripsi::withoutEvents(fn (): Collection => Skripsi::factory()->count(3)->create());

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

    $result = app(SimilarityFullSyncDispatcher::class)->dispatch();

    Queue::assertPushed(RunFullSimilaritySync::class, function (RunFullSimilaritySync $job): bool {
        return $job->chunk === 100;
    });

    expect($result)->toBe([
        'mode' => 'queued',
        'success' => true,
        'error_message' => null,
    ])
        ->and(SimilaritySyncStatus::query()->count())->toBe(3)
        ->and(SimilaritySyncStatus::query()->where('status', SimilaritySyncStatus::STATUS_PENDING)->count())->toBe(3)
        ->and(SimilaritySyncStatus::query()->whereNotNull('last_synced_at')->count())->toBe(0)
        ->and(SimilaritySyncStatus::query()->whereNotNull('last_error')->count())->toBe(0);
});

it('full similarity sync job runs the reset command', function () {
    Artisan::spy();

    $job = new RunFullSimilaritySync(250);
    $job->handle();

    Artisan::shouldHaveReceived('call')
        ->once()
        ->with('skripsi:sync --chunk=250 --reset');
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

    expect($result)->toBe([
        'mode' => 'sync',
        'success' => true,
        'error_message' => null,
    ])
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

    expect($result)->toBe([
        'mode' => 'sync',
        'success' => false,
        'error_message' => 'Forced dispatcher failure',
    ]);
});
