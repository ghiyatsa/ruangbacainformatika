<?php

use App\Services\SimilarityFullSyncDispatcher;
use Illuminate\Support\Facades\Artisan;

test('similarity full sync dispatcher queues the sync command when not running synchronously', function () {
    config()->set('app.env', 'production');

    Artisan::shouldReceive('queue')
        ->once()
        ->with('skripsi:sync', [
            '--chunk' => 100,
        ]);

    $result = app(SimilarityFullSyncDispatcher::class)->dispatch();

    expect($result)->toBe([
        'mode' => 'queued',
        'success' => true,
    ]);
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
