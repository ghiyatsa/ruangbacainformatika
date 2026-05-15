<?php

use App\Jobs\SyncSkripsiToSimilarity;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use App\Services\SimilarityApiService;
use App\Services\SimilaritySyncDispatcher;
use App\Services\SimilaritySyncStatusService;
use Illuminate\Support\Facades\Queue;

test('similarity sync dispatcher queues jobs when configured to queued mode', function () {
    Queue::fake();
    config()->set('services.similarity_api.dispatch', 'queued');

    $skripsi = Skripsi::factory()->create();

    app(SimilaritySyncStatusService::class)->markQueued($skripsi);
    app(SimilaritySyncDispatcher::class)->dispatchUpsert($skripsi->id);

    Queue::assertPushed(SyncSkripsiToSimilarity::class, function (SyncSkripsiToSimilarity $job) use ($skripsi): bool {
        return $job->skripsiId === $skripsi->id;
    });
});

test('similarity sync dispatcher can run jobs immediately in sync mode', function () {
    config()->set('services.similarity_api.dispatch', 'sync');

    $skripsi = Skripsi::withoutEvents(fn (): Skripsi => Skripsi::factory()->create());
    $statusService = app(SimilaritySyncStatusService::class);

    $statusService->markQueued($skripsi);

    $api = Mockery::mock(SimilarityApiService::class);
    $api->shouldReceive('upsert')
        ->once()
        ->andReturn(true);

    app()->instance(SimilarityApiService::class, $api);

    app(SimilaritySyncDispatcher::class)->dispatchUpsert($skripsi->id);

    $status = SimilaritySyncStatus::query()
        ->where('source_skripsi_id', $skripsi->id)
        ->firstOrFail();

    expect($status->status)->toBe(SimilaritySyncStatus::STATUS_SYNCED)
        ->and($status->attempts)->toBe(1)
        ->and($status->last_synced_at)->not->toBeNull();
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
