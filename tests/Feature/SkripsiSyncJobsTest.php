<?php

use App\Jobs\RemoveSkripsiFromSimilarity;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use App\Services\SimilarityApiService;
use App\Services\SimilaritySyncStatusService;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config()->set('services.similarity_api.dispatch', 'queued');
});

it('successful similarity deletion removes the orphan status record', function () {
    Queue::fake();

    $skripsi = Skripsi::withoutEvents(fn (): Skripsi => Skripsi::factory()->create());
    $statusService = app(SimilaritySyncStatusService::class);

    $statusService->markQueued($skripsi, SimilaritySyncStatus::OPERATION_DELETE);
    $skripsi->delete();

    $api = Mockery::mock(SimilarityApiService::class);
    $api->shouldReceive('delete')
        ->once()
        ->with($skripsi->id)
        ->andReturn(true);

    $job = new RemoveSkripsiFromSimilarity($skripsi->id);
    $job->handle($api, $statusService);

    expect(SimilaritySyncStatus::query()
        ->where('source_skripsi_id', $skripsi->id)
        ->exists())->toBeFalse();
});

it('sends deletion once for unsynced skripsi records without scanning indexed ids first', function () {
    Queue::fake();

    $skripsi = Skripsi::withoutEvents(fn (): Skripsi => Skripsi::factory()->create());
    $statusService = app(SimilaritySyncStatusService::class);

    $statusService->markQueued($skripsi, SimilaritySyncStatus::OPERATION_DELETE);
    $skripsi->delete();

    $api = Mockery::mock(SimilarityApiService::class);
    $api->shouldReceive('delete')
        ->once()
        ->with($skripsi->id)
        ->andReturn(true);

    $job = new RemoveSkripsiFromSimilarity($skripsi->id);
    $job->handle($api, $statusService);

    expect(SimilaritySyncStatus::query()
        ->where('source_skripsi_id', $skripsi->id)
        ->exists())->toBeFalse();
});

it('keeps status failed when deletion cannot be confirmed by the api', function () {
    Queue::fake();

    $skripsi = Skripsi::withoutEvents(fn (): Skripsi => Skripsi::factory()->create());
    $statusService = app(SimilaritySyncStatusService::class);

    $statusService->markQueued($skripsi, SimilaritySyncStatus::OPERATION_DELETE);
    $skripsi->delete();

    $api = Mockery::mock(SimilarityApiService::class);
    $api->shouldReceive('delete')
        ->once()
        ->with($skripsi->id)
        ->andReturn(false);

    $job = new RemoveSkripsiFromSimilarity($skripsi->id);
    try {
        $job->handle($api, $statusService);
    } catch (RuntimeException $exception) {
        $job->failed($exception);
    }

    expect(SimilaritySyncStatus::query()
        ->where('source_skripsi_id', $skripsi->id)
        ->value('status'))->toBe(SimilaritySyncStatus::STATUS_FAILED);
});
