<?php

use App\Jobs\RunFullSimilaritySync;
use App\Services\SimilarityFullSyncDispatcher;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;

test('similarity full sync dispatcher queues the sync command when not running synchronously', function () {
    config()->set('app.env', 'production');
    Queue::fake();

    $result = app(SimilarityFullSyncDispatcher::class)->dispatch();

    Queue::assertPushed(RunFullSimilaritySync::class, function (RunFullSimilaritySync $job): bool {
        return $job->chunk === 100;
    });

    expect($result)->toBe([
        'mode' => 'queued',
        'success' => true,
    ]);
});

test('full similarity sync job runs the reset command', function () {
    Artisan::spy();

    $job = new RunFullSimilaritySync(250);
    $job->handle();

    Artisan::shouldHaveReceived('call')
        ->once()
        ->with('skripsi:sync --chunk=250 --reset');
});
