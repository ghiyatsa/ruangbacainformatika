<?php

namespace App\Observers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\InternshipReport;
use App\Models\Publisher;
use App\Models\Skripsi;
use App\Models\Thesis;
use App\Services\ActivityLogService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CatalogActivityObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Model $model): void
    {
        $this->activityLog()->log(
            'catalog.'.$this->subjectKey($model).'.created',
            $this->modelLabel($model).' ditambahkan',
            $model,
            [
                'record' => $this->recordSummary($model),
            ],
        );
    }

    public function updated(Model $model): void
    {
        $changes = Collection::make($model->getChanges())
            ->except($this->ignoredChangeKeys())
            ->all();

        if ($changes === []) {
            return;
        }

        $this->activityLog()->log(
            'catalog.'.$this->subjectKey($model).'.updated',
            $this->modelLabel($model).' diperbarui',
            $model,
            [
                'record' => $this->recordSummary($model),
                'changes' => $changes,
            ],
        );
    }

    public function deleted(Model $model): void
    {
        $this->activityLog()->log(
            'catalog.'.$this->subjectKey($model).'.deleted',
            $this->modelLabel($model).' dihapus',
            $model,
            [
                'record' => $this->recordSummary($model),
            ],
        );
    }

    protected function activityLog(): ActivityLogService
    {
        return app(ActivityLogService::class);
    }

    protected function modelLabel(Model $model): string
    {
        return match ($model::class) {
            Book::class => 'Buku',
            Author::class => 'Penulis',
            Category::class => 'Kategori',
            Publisher::class => 'Penerbit',
            Skripsi::class => 'Skripsi',
            Thesis::class => 'Tesis',
            InternshipReport::class => 'Laporan KP',
            default => class_basename($model),
        };
    }

    protected function subjectKey(Model $model): string
    {
        return Str::of($this->modelLabel($model))->lower()->slug('_')->toString();
    }

    /**
     * @return list<string>
     */
    protected function ignoredChangeKeys(): array
    {
        return [
            'updated_at',
            'view_count',
            'cover_image_editor_state',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function recordSummary(Model $model): array
    {
        return Arr::whereNotNull([
            'id' => $model->getKey(),
            'label' => $model->getAttribute('title')
                ?? $model->getAttribute('name')
                ?? $model->getAttribute('author_name')
                ?? $model->getAttribute('email'),
            'student_id' => $model->getAttribute('student_id'),
            'slug' => $model->getAttribute('slug'),
        ]);
    }
}
