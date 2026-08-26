<?php

use App\Actions\Similarity\CheckSimilarity;
use App\Jobs\RemoveSkripsiFromSimilarity;
use App\Jobs\SyncSkripsiChunkToSimilarity;
use App\Jobs\SyncSkripsiToSimilarity;
use App\Models\InternshipReport;
use App\Models\Setting;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use App\Models\User;
use App\Services\SimilarityApiService;
use App\Services\SimilarityFullSyncDispatcher;
use App\Services\SimilaritySyncDispatcher;
use App\Services\SimilaritySyncStatusService;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;

// =========================================================================
// SECTION 1: Similarity API Service Tests
// =========================================================================

it('api service retries transient failures', function () {
    Setting::query()->create(['section' => 'integration', 'key' => 'similarity_api_url', 'value' => 'https://similarity.test']);
    Setting::query()->create(['section' => 'integration', 'key' => 'similarity_api_secret', 'value' => encrypt('sync-secret')]);

    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;

        return $attempts === 1 ? Http::response([], 503) : Http::response(['message' => 'ok'], 200);
    });

    $result = app(SimilarityApiService::class)->upsert(['judul' => 'Judul skripsi test']);
    expect($result)->toBeTrue()->and($attempts)->toBe(2);
});

it('api service does not retry unauthorized failures', function () {
    Setting::query()->create(['section' => 'integration', 'key' => 'similarity_api_url', 'value' => 'https://similarity.test']);
    Setting::query()->create(['section' => 'integration', 'key' => 'similarity_api_secret', 'value' => encrypt('sync-secret')]);

    $attempts2 = 0;
    Http::fake(function () use (&$attempts2) {
        $attempts2++;

        return Http::response([], 401);
    });
    $result2 = app(SimilarityApiService::class)->upsert(['judul' => 'Judul skripsi test']);
    expect($result2)->toBeFalse()->and($attempts2)->toBe(1);
});

it('api service handles bulk upsert job polling and deletes', function () {
    Setting::query()->create(['section' => 'integration', 'key' => 'similarity_api_url', 'value' => 'https://similarity.test']);
    Setting::query()->create(['section' => 'integration', 'key' => 'similarity_api_secret', 'value' => encrypt('sync-secret')]);

    Http::fake([
        'https://similarity.test/api/v1/sync/bulk-upsert' => Http::response(['status' => 'accepted', 'job_id' => 'job-123'], 202),
        'https://similarity.test/api/v1/sync/jobs/job-123' => Http::sequence()
            ->push(['status' => 'processing'])
            ->push(['status' => 'completed']),
        'https://similarity.test/api/v1/sync/77' => Http::response([], 404),
    ]);

    $result = app(SimilarityApiService::class)->bulkUpsert([['judul' => 'Test 1']]);
    expect($result)->toBeTrue();

    $deleteResult = app(SimilarityApiService::class)->delete(77);
    expect($deleteResult)->toBeTrue();
});

it('bulk upsert throws with the job failure reason', function () {
    Setting::query()->create(['section' => 'integration', 'key' => 'similarity_api_url', 'value' => 'https://similarity.test']);
    Setting::query()->create(['section' => 'integration', 'key' => 'similarity_api_secret', 'value' => encrypt('sync-secret')]);

    Http::fake([
        'https://similarity.test/api/v1/sync/bulk-upsert' => Http::response(['status' => 'accepted', 'job_id' => 'job-fail'], 202),
        'https://similarity.test/api/v1/sync/jobs/job-fail' => Http::response(['status' => 'failed', 'error' => 'model not loaded'], 200),
    ]);

    expect(fn () => app(SimilarityApiService::class)->bulkUpsert([['judul' => 'Test']]))
        ->toThrow(RuntimeException::class, 'Bulk job Similarity API gagal');
});

it('chunk job records the real failure reason', function () {
    $skripsis = Skripsi::withoutEvents(fn () => Skripsi::factory()->count(2)->create());

    $api = Mockery::mock(SimilarityApiService::class);
    $api->shouldReceive('bulkUpsert')
        ->once()
        ->andThrow(new RuntimeException('Koneksi ke Similarity API gagal: connection refused'));

    $job = new SyncSkripsiChunkToSimilarity($skripsis->pluck('id')->all(), false, Skripsi::class);

    expect(fn () => $job->handle($api, app(SimilaritySyncStatusService::class)))
        ->toThrow(RuntimeException::class, 'Koneksi ke Similarity API gagal');

    $statuses = SimilaritySyncStatus::query()
        ->where('status', SimilaritySyncStatus::STATUS_FAILED)
        ->get();

    expect($statuses)->toHaveCount(2);

    foreach ($statuses as $status) {
        expect($status->last_error)->toContain('Koneksi ke Similarity API gagal');
    }
});

// =========================================================================
// SECTION 2: Controller & Checker Tests
// =========================================================================

it('renders similarity index and checks validation', function () {
    actingAs(User::factory()->create())
        ->get(route('similarity.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('similarity/index'));

    actingAs(User::factory()->create())
        ->postJson(route('similarity.check'), ['judul' => 'Judul pendek'])
        ->assertStatus(422);
});

it('normalizes similarity results from api for both skripsi and internship_report', function () {
    $skripsi = Skripsi::withoutEvents(fn () => Skripsi::factory()->create(['id' => 99, 'title' => 'Skripsi Sentimen', 'student_id' => 'NIM123']));
    $report = InternshipReport::withoutEvents(fn () => InternshipReport::factory()->create(['id' => 88, 'title' => 'Laporan KP Magang', 'student_id' => 'NIM456']));

    $service = Mockery::mock(SimilarityApiService::class);
    $service->shouldReceive('checkSimilarity')->once()->andReturn([
        'total_found' => 2,
        'results' => [
            ['document_type' => 'skripsi', 'document_id' => 99, 'nim' => 'NIM123', 'similarity_score' => 0.95],
            ['document_type' => 'internship_report', 'document_id' => 88, 'nim' => 'NIM456', 'similarity_score' => 0.85],
        ],
    ]);
    app()->instance(SimilarityApiService::class, $service);

    actingAs(User::factory()->create())
        ->postJson(route('similarity.check'), ['judul' => 'Cek kemiripan judul dengan lima kata lengkap'])
        ->assertOk()
        ->assertJsonFragment(['skripsi_id' => 99, 'judul' => 'Skripsi Sentimen', 'document_type' => 'skripsi'])
        ->assertJsonFragment(['judul' => 'Laporan KP Magang', 'document_type' => 'internship_report']);
});

it('caches check results and serves fresh data after sync invalidates the cache', function () {
    $service = Mockery::mock(SimilarityApiService::class);
    $service->shouldReceive('checkSimilarity')->twice()->andReturn(
        ['total_found' => 0, 'results' => []],
        ['total_found' => 1, 'results' => [['document_type' => 'skripsi', 'document_id' => 5, 'similarity_score' => 0.9]]],
    );
    app()->instance(SimilarityApiService::class, $service);

    $payload = ['judul' => 'Analisis sentimen ulasan aplikasi mobile'];

    actingAs(User::factory()->create())
        ->postJson(route('similarity.check'), $payload)
        ->assertOk()
        ->assertJsonFragment(['total_found' => 0]);

    // Hasil kedua sebelum invalidation masih dari cache (API hanya dipanggil 2x total).
    actingAs(User::factory()->create())
        ->postJson(route('similarity.check'), $payload)
        ->assertOk()
        ->assertJsonFragment(['total_found' => 0]);

    CheckSimilarity::invalidateCache();

    actingAs(User::factory()->create())
        ->postJson(route('similarity.check'), $payload)
        ->assertOk()
        ->assertJsonFragment(['total_found' => 1]);
});

it('throttles the similarity check endpoint', function () {
    $route = Route::getRoutes()->getByName('similarity.check');

    expect($route->gatherMiddleware())->toContain('throttle:similarity-check');
});

// =========================================================================
// SECTION 3: Sync Status & Job Tests
// =========================================================================

it('sync status service tracks queued, syncing, synced, and failed status polimorfik', function () {
    $skripsi = Skripsi::withoutEvents(fn () => Skripsi::factory()->create());
    $report = InternshipReport::withoutEvents(fn () => InternshipReport::factory()->create());
    $service = app(SimilaritySyncStatusService::class);

    // markQueued
    $service->markQueued($skripsi);
    $service->markQueued($report);
    expect(SimilaritySyncStatus::query()->count())->toBe(2);

    // markProcessing
    $service->markProcessing($skripsi);
    expect($skripsi->similaritySyncStatus->status)->toBe(SimilaritySyncStatus::STATUS_SYNCING);

    // markSynced
    $service->markSynced($skripsi);
    expect($skripsi->fresh()->similaritySyncStatus->status)->toBe(SimilaritySyncStatus::STATUS_SYNCED);

    // markFailed
    $service->markFailed($report, 'API timeout error');
    expect($report->fresh()->similaritySyncStatus->status)->toBe(SimilaritySyncStatus::STATUS_FAILED)
        ->and($report->fresh()->similaritySyncStatus->last_error)->toBe('API timeout error');
});

it('sync jobs sync models and delete orphan status', function () {
    $skripsi = Skripsi::withoutEvents(fn () => Skripsi::factory()->create());
    $statusService = app(SimilaritySyncStatusService::class);
    $statusService->markQueued($skripsi);

    $api = Mockery::mock(SimilarityApiService::class);
    $api->shouldReceive('upsert')->once()->andReturn(true);

    $job = new SyncSkripsiToSimilarity($skripsi->id, Skripsi::class);
    $job->handle($api, $statusService);

    expect(SimilaritySyncStatus::query()->where('syncable_id', $skripsi->id)->value('status'))->toBe(SimilaritySyncStatus::STATUS_SYNCED);

    // Delete job
    $apiDelete = Mockery::mock(SimilarityApiService::class);
    $apiDelete->shouldReceive('delete')->once()->with("skripsi_{$skripsi->id}")->andReturn(true);

    $deleteJob = new RemoveSkripsiFromSimilarity($skripsi->id, Skripsi::class);
    $deleteJob->handle($apiDelete, $statusService);

    expect(SimilaritySyncStatus::query()->where('syncable_id', $skripsi->id)->exists())->toBeFalse();
});

// =========================================================================
// SECTION 4: Full Sync Dispatcher & Bulk Jobs Tests
// =========================================================================

it('full sync dispatcher dispatches batch jobs for both models', function () {
    app()['env'] = 'production';
    config()->set('queue.default', 'database');
    Bus::fake();

    $skripsis = Skripsi::withoutEvents(fn () => Skripsi::factory()->count(3)->create());
    $reports = InternshipReport::withoutEvents(fn () => InternshipReport::factory()->count(2)->create());

    $result = app(SimilarityFullSyncDispatcher::class)->dispatch(chunk: 2);

    Bus::assertBatched(function (PendingBatch $batch): bool {
        // Skripsi has 3 records -> 2 jobs (chunk size 2)
        // InternshipReport has 2 records -> 1 job
        return $batch->name === 'similarity-full-sync'
            && $batch->jobs->count() === 3
            && $batch->jobs[0]->modelClass === Skripsi::class
            && $batch->jobs[2]->modelClass === InternshipReport::class;
    });

    expect($result['success'])->toBeTrue()
        ->and(SimilaritySyncStatus::query()->count())->toBe(5)
        ->and(SimilaritySyncStatus::query()->where('status', SimilaritySyncStatus::STATUS_PENDING)->count())->toBe(5);
});

it('bulk chunk job pushes multiple records to API', function () {
    $skripsis = Skripsi::withoutEvents(fn () => Skripsi::factory()->count(2)->create());

    $api = Mockery::mock(SimilarityApiService::class);
    $api->shouldReceive('bulkUpsert')
        ->once()
        ->withArgs(fn (array $items, bool $reset) => count($items) === 2 && $reset)
        ->andReturn(true);

    $job = new SyncSkripsiChunkToSimilarity($skripsis->pluck('id')->all(), true, Skripsi::class);
    $job->handle($api, app(SimilaritySyncStatusService::class));

    expect(SimilaritySyncStatus::query()->where('status', SimilaritySyncStatus::STATUS_SYNCED)->count())->toBe(2);
});

// =========================================================================
// SECTION 5: Single Model Sync Dispatcher Tests
// =========================================================================

it('sync dispatcher queues single model upsert and delete', function () {
    Queue::fake();
    config()->set('services.similarity_api.dispatch', 'queued');

    $skripsi = Skripsi::withoutEvents(fn () => Skripsi::factory()->create());
    app(SimilaritySyncDispatcher::class)->dispatchUpsert($skripsi->id);

    Queue::assertPushed(SyncSkripsiToSimilarity::class, function (SyncSkripsiToSimilarity $job) use ($skripsi) {
        return $job->skripsiId === $skripsi->id && $job->modelClass === Skripsi::class;
    });
});

it('sync dispatcher queues multiple model bulk upsert', function () {
    Queue::fake();
    config()->set('services.similarity_api.dispatch', 'queued');

    $skripsis = Skripsi::withoutEvents(fn () => Skripsi::factory()->count(3)->create());
    $ids = $skripsis->pluck('id')->all();

    app(SimilaritySyncStatusService::class)->markQueuedMultiple($ids, SimilaritySyncStatus::OPERATION_UPSERT, Skripsi::class);
    expect(SimilaritySyncStatus::query()->where('status', SimilaritySyncStatus::STATUS_PENDING)->count())->toBe(3);

    app(SimilaritySyncDispatcher::class)->dispatchBulkUpsert($ids, Skripsi::class);

    Queue::assertPushed(SyncSkripsiChunkToSimilarity::class, function (SyncSkripsiChunkToSimilarity $job) use ($ids) {
        return $job->skripsiIds === $ids && $job->modelClass === Skripsi::class;
    });
});

// =========================================================================
// SECTION 6: Delete Observer & Cache Invalidation Tests
// =========================================================================

it('deleting skripsi or internship report dispatches removal from similarity api', function () {
    Queue::fake();
    config()->set('services.similarity_api.dispatch', 'queued');

    $skripsi = Skripsi::withoutEvents(fn () => Skripsi::factory()->create());
    $report = InternshipReport::withoutEvents(fn () => InternshipReport::factory()->create());

    $skripsi->delete();
    $report->delete();

    Queue::assertPushed(RemoveSkripsiFromSimilarity::class, 2);

    Queue::assertPushed(RemoveSkripsiFromSimilarity::class, function (RemoveSkripsiFromSimilarity $job) use ($skripsi) {
        return $job->skripsiId === $skripsi->id && $job->modelClass === Skripsi::class;
    });

    Queue::assertPushed(RemoveSkripsiFromSimilarity::class, function (RemoveSkripsiFromSimilarity $job) use ($report) {
        return $job->skripsiId === $report->id && $job->modelClass === InternshipReport::class;
    });
});

it('sync and delete jobs invalidate the similarity check cache version', function () {
    $versionKey = 'similarity_check_version';

    expect(Cache::get($versionKey))->toBeNull();

    $skripsi = Skripsi::withoutEvents(fn () => Skripsi::factory()->create());

    $api = Mockery::mock(SimilarityApiService::class);
    $api->shouldReceive('upsert')->once()->andReturn(true);

    (new SyncSkripsiToSimilarity($skripsi->id, Skripsi::class))->handle($api, app(SimilaritySyncStatusService::class));
    expect(Cache::get($versionKey))->toBe(1);

    $apiDelete = Mockery::mock(SimilarityApiService::class);
    $apiDelete->shouldReceive('delete')->once()->with("skripsi_{$skripsi->id}")->andReturn(true);

    (new RemoveSkripsiFromSimilarity($skripsi->id, Skripsi::class))->handle($apiDelete, app(SimilaritySyncStatusService::class));
    expect(Cache::get($versionKey))->toBe(2);
});
