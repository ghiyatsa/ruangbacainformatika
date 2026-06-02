<?php

namespace App\Listeners;

use App\Filament\Imports\SkripsiImporter;
use App\Models\Skripsi;
use App\Services\SimilaritySyncDispatcher;
use Filament\Actions\Imports\Events\ImportCompleted;
use Illuminate\Support\Facades\Cache;

class DispatchSimilaritySyncAfterSkripsiImport
{
    public function __construct(
        protected SimilaritySyncDispatcher $dispatcher,
    ) {}

    public function handle(ImportCompleted $event): void
    {
        $import = $event->getImport();

        if ($import->importer !== SkripsiImporter::class) {
            return;
        }

        $cacheKey = SkripsiImporter::importedIdsCacheKey($import->getKey());
        $skripsiIds = collect(Cache::pull($cacheKey, []))
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();

        if ($skripsiIds->isEmpty()) {
            return;
        }

        Skripsi::query()
            ->whereKey($skripsiIds->all())
            ->select('id')
            ->chunkById(100, function ($skripsis): void {
                foreach ($skripsis as $skripsi) {
                    $this->dispatcher->dispatchUpsert($skripsi->id);
                }
            });
    }
}
