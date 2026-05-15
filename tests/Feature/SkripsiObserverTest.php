<?php

use App\Jobs\RemoveSkripsiFromSimilarity;
use App\Jobs\SyncSkripsiToSimilarity;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use Illuminate\Support\Facades\Queue;

test('creating a skripsi queues a similarity sync job', function () {
    Queue::fake();

    $skripsi = Skripsi::factory()->create();

    Queue::assertPushed(SyncSkripsiToSimilarity::class, function (SyncSkripsiToSimilarity $job) use ($skripsi): bool {
        return $job->skripsiId === $skripsi->id;
    });

    expect(SimilaritySyncStatus::query()
        ->where('source_skripsi_id', $skripsi->id)
        ->value('status'))
        ->toBe(SimilaritySyncStatus::STATUS_PENDING);
});

test('updating a skripsi queues a similarity sync job', function () {
    Queue::fake();

    $skripsi = Skripsi::factory()->create();

    Queue::fake();

    $skripsi->update([
        'title' => 'Judul skripsi yang sudah diperbarui',
    ]);

    Queue::assertPushed(SyncSkripsiToSimilarity::class, function (SyncSkripsiToSimilarity $job) use ($skripsi): bool {
        return $job->skripsiId === $skripsi->id;
    });

    expect(SimilaritySyncStatus::query()
        ->where('source_skripsi_id', $skripsi->id)
        ->value('status'))
        ->toBe(SimilaritySyncStatus::STATUS_PENDING);
});

test('deleting a skripsi queues a similarity removal job', function () {
    Queue::fake();

    $skripsi = Skripsi::factory()->create();

    Queue::fake();

    $skripsi->delete();

    Queue::assertPushed(RemoveSkripsiFromSimilarity::class, function (RemoveSkripsiFromSimilarity $job) use ($skripsi): bool {
        return $job->skripsiId === $skripsi->id;
    });

    expect(SimilaritySyncStatus::query()
        ->where('source_skripsi_id', $skripsi->id)
        ->value('last_operation'))
        ->toBe(SimilaritySyncStatus::OPERATION_DELETE);
});
