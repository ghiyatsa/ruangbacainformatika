<?php

namespace App\Services;

use App\Jobs\RemoveSkripsiFromSimilarity;
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
        return match (config('services.similarity_api.dispatch', 'auto')) {
            'sync' => true,
            'queued' => false,
            default => app()->isLocal() && (! app()->runningUnitTests()) && (config('queue.default') !== 'sync'),
        };
    }
}
