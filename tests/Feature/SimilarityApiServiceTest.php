<?php

use App\Models\Setting;
use App\Services\SimilarityApiService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('similarity api service retries transient upstream failures', function () {
    Setting::query()->create([
        'section' => 'integration',
        'key' => 'similarity_api_url',
        'value' => 'https://similarity.test',
    ]);

    Setting::query()->create([
        'section' => 'integration',
        'key' => 'similarity_api_secret',
        'value' => encrypt('sync-secret'),
    ]);
    Setting::query()->create(['section' => 'integration', 'key' => 'similarity_weight_judul', 'value' => '0.6']);
    Setting::query()->create(['section' => 'integration', 'key' => 'similarity_weight_abstrak', 'value' => '0.3']);
    Setting::query()->create(['section' => 'integration', 'key' => 'similarity_weight_kata_kunci', 'value' => '0.1']);

    $attempts = 0;

    Http::fake(function (Request $request) use (&$attempts) {
        $attempts++;

        expect($request->url())->toBe('https://similarity.test/api/v1/sync/upsert');
        expect($request['bobot_judul'])->toBe(0.6);
        expect($request['bobot_abstrak'])->toBe(0.3);
        expect($request['bobot_kata_kunci'])->toBe(0.1);

        return $attempts === 1
            ? Http::response(['message' => 'temporary failure'], 503)
            : Http::response(['message' => 'ok'], 200);
    });

    $result = app(SimilarityApiService::class)->upsert([
        'skripsi_id' => 1,
        'judul' => 'Judul skripsi yang cukup panjang untuk diuji',
    ]);

    expect($result)->toBeTrue();
    expect($attempts)->toBe(2);
});

test('similarity api service does not retry invalid credentials responses', function () {
    Setting::query()->create([
        'section' => 'integration',
        'key' => 'similarity_api_url',
        'value' => 'https://similarity.test',
    ]);

    Setting::query()->create([
        'section' => 'integration',
        'key' => 'similarity_api_secret',
        'value' => encrypt('stale-secret'),
    ]);

    $attempts = 0;

    Http::fake(function () use (&$attempts) {
        $attempts++;

        return Http::response(['message' => 'unauthorized'], 401);
    });

    $result = app(SimilarityApiService::class)->upsert([
        'skripsi_id' => 2,
        'judul' => 'Judul lain yang juga cukup panjang untuk diuji',
    ]);

    expect($result)->toBeFalse();
    expect($attempts)->toBe(1);
});

test('similarity api service waits for bulk upsert job completion', function () {
    Setting::query()->create([
        'section' => 'integration',
        'key' => 'similarity_api_url',
        'value' => 'https://similarity.test',
    ]);

    Setting::query()->create([
        'section' => 'integration',
        'key' => 'similarity_api_secret',
        'value' => encrypt('sync-secret'),
    ]);
    Setting::query()->create(['section' => 'integration', 'key' => 'similarity_weight_judul', 'value' => '0.65']);
    Setting::query()->create(['section' => 'integration', 'key' => 'similarity_weight_abstrak', 'value' => '0.25']);
    Setting::query()->create(['section' => 'integration', 'key' => 'similarity_weight_kata_kunci', 'value' => '0.10']);

    $requests = [];

    Http::fake(function (Request $request) use (&$requests) {
        $requests[] = $request->url();

        if ($request->url() === 'https://similarity.test/api/v1/sync/bulk-upsert') {
            expect($request['data'][0]['bobot_judul'])->toBe(0.65);
            expect($request['data'][0]['bobot_abstrak'])->toBe(0.25);
            expect($request['data'][0]['bobot_kata_kunci'])->toBe(0.1);
            expect($request['data'][1]['bobot_judul'])->toBe(0.65);
            expect($request['reset_index'])->toBeFalse();
        }

        return match ($request->url()) {
            'https://similarity.test/api/v1/sync/bulk-upsert' => Http::response([
                'status' => 'accepted',
                'job_id' => 'job-123',
            ], 202),
            'https://similarity.test/api/v1/sync/jobs/job-123' => count($requests) < 3
                ? Http::response([
                    'job_id' => 'job-123',
                    'status' => 'processing',
                    'total_received' => 2,
                    'total_processed' => 1,
                ], 200)
                : Http::response([
                    'job_id' => 'job-123',
                    'status' => 'completed',
                    'total_received' => 2,
                    'total_processed' => 2,
                ], 200),
            default => Http::response(['message' => 'unexpected'], 500),
        };
    });

    $result = app(SimilarityApiService::class)->bulkUpsert([
        [
            'skripsi_id' => 10,
            'judul' => 'Judul pertama yang cukup panjang untuk batch sync',
        ],
        [
            'skripsi_id' => 11,
            'judul' => 'Judul kedua yang cukup panjang untuk batch sync',
        ],
    ]);

    expect($result)->toBeTrue();
    expect($requests)->toBe([
        'https://similarity.test/api/v1/sync/bulk-upsert',
        'https://similarity.test/api/v1/sync/jobs/job-123',
        'https://similarity.test/api/v1/sync/jobs/job-123',
    ]);
});

test('similarity api service can request a full reset before bulk upsert', function () {
    Setting::query()->create([
        'section' => 'integration',
        'key' => 'similarity_api_url',
        'value' => 'https://similarity.test',
    ]);

    Setting::query()->create([
        'section' => 'integration',
        'key' => 'similarity_api_secret',
        'value' => encrypt('sync-secret'),
    ]);

    $requests = [];

    Http::fake(function (Request $request) use (&$requests) {
        $requests[] = $request->url();

        return match ($request->url()) {
            'https://similarity.test/api/v1/sync/bulk-upsert' => tap(
                Http::response([
                    'status' => 'accepted',
                    'job_id' => 'job-reset',
                ], 202),
                function () use ($request): void {
                    expect($request['reset_index'])->toBeTrue();
                },
            ),
            'https://similarity.test/api/v1/sync/jobs/job-reset' => Http::response([
                'job_id' => 'job-reset',
                'status' => 'completed',
                'total_received' => 1,
                'total_processed' => 1,
            ], 200),
            default => Http::response(['message' => 'unexpected'], 500),
        };
    });

    $result = app(SimilarityApiService::class)->bulkUpsert([
        [
            'skripsi_id' => 10,
            'judul' => 'Judul reset penuh yang cukup panjang untuk batch sync',
        ],
    ], true);

    expect($result)->toBeTrue();
    expect($requests)->toBe([
        'https://similarity.test/api/v1/sync/bulk-upsert',
        'https://similarity.test/api/v1/sync/jobs/job-reset',
    ]);
});
