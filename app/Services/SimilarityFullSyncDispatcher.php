<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;

class SimilarityFullSyncDispatcher
{
    /**
     * @return array{mode: 'sync'|'queued', success: bool}
     */
    public function dispatch(int $chunk = 100): array
    {
        if ($this->shouldRunSynchronously()) {
            $exitCode = Artisan::call('skripsi:sync', [
                '--chunk' => $chunk,
            ]);

            return [
                'mode' => 'sync',
                'success' => $exitCode === 0,
            ];
        }

        Artisan::queue('skripsi:sync', [
            '--chunk' => $chunk,
        ]);

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
