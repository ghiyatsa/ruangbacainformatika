<?php

namespace App\Services;

use App\Jobs\RunFullSimilaritySync;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class SimilarityFullSyncDispatcher
{
    public function __construct(
        private readonly SimilaritySyncStatusService $statusService,
    ) {}

    /**
     * @return array{mode: 'sync'|'queued', success: bool, error_message?: string|null}
     */
    public function dispatch(int $chunk = 100, bool $forceSync = false): array
    {
        try {
            $command = sprintf('skripsi:sync --chunk=%d --reset', $chunk);

            if ($forceSync || $this->shouldRunSynchronously()) {
                $this->statusService->markAllQueuedForFullSync();
                $exitCode = Artisan::call($command);

                return [
                    'mode' => 'sync',
                    'success' => $exitCode === 0,
                    'error_message' => $exitCode === 0 ? null : 'Sinkronisasi penuh berhenti sebelum semua data selesai diproses.',
                ];
            }

            $this->statusService->markAllQueuedForFullSync();
            RunFullSimilaritySync::dispatch($chunk);

            return [
                'mode' => 'queued',
                'success' => true,
                'error_message' => null,
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'mode' => 'sync',
                'success' => false,
                'error_message' => $exception->getMessage(),
            ];
        }
    }

    private function shouldRunSynchronously(): bool
    {
        return app()->isLocal()
            && (! app()->runningUnitTests())
            && (config('queue.default') !== 'sync');
    }
}
