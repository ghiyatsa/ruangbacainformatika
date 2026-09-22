<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Models\Post;
use App\Models\PostTag;
use App\Services\Post\PostRevisionService;

/**
 * Membantu form artikel menulis perubahan ke revisi tertunda, bukan langsung
 * ke artikel yang sudah terbit.
 */
trait ManagesPostRevisionForm
{
    /**
     * Apakah perubahan pada record ini harus masuk ke revisi tertunda.
     */
    abstract protected static function shouldStageRevision(?Post $record): bool;

    /**
     * Simpan relasi kategori/tag ke revisi bila sedang meninjau perubahan,
     * atau langsung ke artikel bila tidak.
     *
     * @param  array<int, mixed>  $state
     */
    protected static function stageOrSyncRelation(?Post $record, string $relation, array $state): void
    {
        if ($record === null) {
            return;
        }

        if (static::shouldStageRevision($record)) {
            app(PostRevisionService::class)->stageRelation($record, $relation, $state);

            return;
        }

        if ($relation === 'categories') {
            $record->categories()->sync(
                collect($state)->filter(fn ($id): bool => filled($id))->map(fn ($id): int => (int) $id)->all(),
            );

            return;
        }

        $tagIds = [];
        foreach ($state as $tagName) {
            $tagIds[] = PostTag::findOrCreateByName((string) $tagName)->getKey();
        }

        $record->tags()->sync($tagIds);
    }
}
