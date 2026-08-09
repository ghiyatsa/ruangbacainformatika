<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;

class AcademicCacheObserver
{
    public function saved(mixed $model): void
    {
        $this->clearCache($model);
    }

    public function deleted(mixed $model): void
    {
        $this->clearCache($model);
    }

    protected function clearCache(mixed $model): void
    {
        $class = class_basename($model);

        if ($class === 'Skripsi') {
            Cache::forget('academic:skripsi:years');
        } elseif ($class === 'Thesis') {
            Cache::forget('academic:thesis:years');
        } elseif ($class === 'InternshipReport') {
            Cache::forget('academic:internship_report:years');
        }
    }
}
