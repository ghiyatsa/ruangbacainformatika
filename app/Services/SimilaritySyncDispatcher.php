<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\RemoveSkripsiFromSimilarity;
use App\Jobs\SyncSkripsiChunkToSimilarity;
use App\Jobs\SyncSkripsiToSimilarity;
use App\Models\Skripsi;
use Illuminate\Support\Facades\DB;
use Throwable;

class SimilaritySyncDispatcher
{
    public function dispatchUpsert(int $id, string $modelClass = Skripsi::class): void
    {
        $this->dispatchJob(
            new SyncSkripsiToSimilarity($id, $modelClass),
            fn (): mixed => SyncSkripsiToSimilarity::dispatch($id, $modelClass),
        );
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function dispatchBulkUpsert(array $ids, string $modelClass = Skripsi::class): void
    {
        if ($ids === []) {
            return;
        }

        $this->dispatchJob(
            new SyncSkripsiChunkToSimilarity($ids, false, $modelClass),
            fn (): mixed => SyncSkripsiChunkToSimilarity::dispatch($ids, false, $modelClass),
        );
    }

    public function dispatchDelete(int $id, string $modelClass = Skripsi::class): void
    {
        $this->dispatchJob(
            new RemoveSkripsiFromSimilarity($id, $modelClass),
            fn (): mixed => RemoveSkripsiFromSimilarity::dispatch($id, $modelClass),
        );
    }

    private function dispatchJob(object $job, callable $queueDispatch): void
    {
        if (! $this->shouldRunSynchronously()) {
            $queueDispatch();

            return;
        }

        $runSynchronously = function () use ($job): void {
            try {
                app()->call([$job, 'handle']);
            } catch (Throwable $exception) {
                report($exception);

                if (method_exists($job, 'failed')) {
                    $job->failed($exception);
                }
            }
        };

        if (DB::transactionLevel() > 0) {
            DB::afterCommit($runSynchronously);

            return;
        }

        $runSynchronously();
    }

    private function shouldRunSynchronously(): bool
    {
        $configured = config('services.similarity_api.dispatch', 'auto');

        // Hormati pilihan eksplisit operator.
        if ($configured === 'sync') {
            return true;
        }

        if ($configured === 'queued') {
            return false;
        }

        // Mode 'auto': jalankan sinkron kecuali queue benar-benar dilayani worker.
        return ! $this->queueHasWorker();
    }

    /**
     * Apakah queue yang aktif benar-benar dilayani worker?
     *
     * Di hosting tanpa worker (mis. server kampus) queue "database"/"redis"
     * menumpuk job tanpa pernah dieksekusi, sehingga sinkronisasi skripsi
     * berhenti diam-diam. Driver "sync" selalu aman karena job dijalankan inline.
     */
    private function queueHasWorker(): bool
    {
        $connection = config('queue.default');

        if (! is_string($connection) || $connection === '' || $connection === 'sync') {
            return false;
        }

        return true;
    }
}
