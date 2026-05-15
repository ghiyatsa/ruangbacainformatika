<?php

namespace App\Services;

use App\Jobs\RunFullSimilaritySync;
use Illuminate\Support\Facades\Artisan;

class SimilarityFullSyncDispatcher
{
    /**
     * @return array{mode: 'sync'|'queued', success: bool}
     */
    public function dispatch(int $chunk = 100): array
    {
        $command = sprintf('skripsi:sync --chunk=%d --reset', $chunk);

        if ($this->shouldRunSynchronously()) {
            $exitCode = Artisan::call($command);

            return [
                'mode' => 'sync',
                'success' => $exitCode === 0,
            ];
        }

        RunFullSimilaritySync::dispatch($chunk);

        return [
            'mode' => 'queued',
            'success' => true,
        ];
    }

    private function shouldRunSynchronously(): bool
    {
        return app()->isLocal() && (! app()->runningUnitTests()) && (config('queue.default') !== 'sync');
    }
}
