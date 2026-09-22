<?php

declare(strict_types=1);

namespace App\Services\Post;

use App\Models\Post;
use App\Models\PostRevision;
use App\Models\PostTag;
use App\Models\User;
use App\Notifications\PostRevisionApprovedNotification;
use App\Notifications\PostRevisionRejectedNotification;
use Illuminate\Support\Facades\DB;

/**
 * Menyimpan suntingan penulis sebagai revisi yang menunggu tinjauan.
 *
 * Sebelumnya suntingan langsung menimpa baris `posts`, sehingga artikel yang
 * sudah terbit otomatis ditarik kembali. Dengan revisi, versi yang sudah
 * disetujui tetap tampil sampai perubahan barunya disetujui peninjau.
 */
class PostRevisionService
{
    /**
     * Revisi yang sedang dikerjakan penulis (belum diterbitkan).
     *
     * Termasuk revisi yang dikembalikan agar penulis dapat melanjutkan
     * perbaikan pada catatan yang sama, bukan memulai dari nol.
     */
    public function workingRevision(Post $post): ?PostRevision
    {
        return $post->revisions()
            ->working()
            ->latest('id')
            ->first();
    }

    /**
     * Revisi yang menunggu keputusan peninjau.
     */
    public function pendingRevision(Post $post): ?PostRevision
    {
        return $post->revisions()
            ->pending()
            ->latest('id')
            ->first();
    }

    public function hasPendingRevision(Post $post): bool
    {
        return $post->revisions()->pending()->exists();
    }

    public function ensureWorkingRevision(Post $post, ?User $user = null): PostRevision
    {
        $revision = $this->workingRevision($post);

        if ($revision !== null) {
            return $revision;
        }

        return $post->revisions()->create([
            'user_id' => $user?->getKey() ?? $post->user_id,
            'status' => PostRevision::STATUS_PENDING,
            'title' => $post->title,
            'slug' => $post->slug,
            'summary' => $post->summary,
            'content' => (string) $post->content,
            'cover_image' => $post->cover_image,
            'allow_comments' => (bool) $post->allow_comments,
            'categories' => $post->categories()->pluck('post_categories.id')->all(),
            'tags' => $post->tags()->pluck('name')->all(),
        ]);
    }

    /**
     * Simpan pilihan kategori/tag ke revisi, bukan ke artikel terbit.
     *
     * @param  array<int, mixed>  $value
     */
    public function stageRelation(Post $post, string $relation, array $value): ?PostRevision
    {
        $normalized = $relation === 'categories'
            ? collect($value)
                ->filter(fn ($id): bool => filled($id))
                ->map(fn ($id): int => (int) $id)
                ->unique()
                ->values()
                ->all()
            : collect($value)
                ->filter(fn ($name): bool => filled($name))
                ->map(fn ($name): string => trim((string) $name))
                ->unique()
                ->values()
                ->all();

        $revision = $this->workingRevision($post);

        if ($revision === null && ! $this->relationDiffers($post, $relation, $normalized)) {
            return null;
        }

        $revision ??= $this->ensureWorkingRevision($post, auth()->user());
        $revision->{$relation} = $normalized;
        $revision->save();

        return $revision;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function stageContent(Post $post, array $data): ?PostRevision
    {
        $revision = $this->workingRevision($post);

        if ($revision === null && ! $this->contentDiffers($post, $data)) {
            return null;
        }

        $revision ??= $this->ensureWorkingRevision($post, auth()->user());

        $revision->fill([
            'title' => $data['title'] ?? $revision->title,
            'slug' => $data['slug'] ?? $revision->slug,
            'summary' => $data['summary'] ?? null,
            'content' => $data['content'] ?? $revision->content,
            'cover_image' => $data['cover_image'] ?? null,
            'allow_comments' => (bool) ($data['allow_comments'] ?? true),
        ]);

        $revision->status = ($data['status'] ?? null) === Post::STATUS_PENDING
            ? PostRevision::STATUS_PENDING
            : PostRevision::STATUS_DRAFT;
        $revision->reviewed_by_user_id = null;
        $revision->reviewed_at = null;
        $revision->rejection_reason = null;
        $revision->save();

        return $revision;
    }

    /**
     * Terapkan revisi ke artikel terbit tanpa menariknya dari publikasi.
     */
    public function approve(PostRevision $revision, User $reviewer): Post
    {
        $post = DB::transaction(function () use ($revision, $reviewer): Post {
            $post = $revision->post()->lockForUpdate()->firstOrFail();

            $post->fill([
                'title' => $revision->title,
                'slug' => $revision->slug,
                'summary' => $revision->summary,
                'content' => $revision->content,
                'cover_image' => $revision->cover_image,
                'allow_comments' => (bool) $revision->allow_comments,
                'status' => Post::STATUS_APPROVED,
                'reviewed_by_user_id' => $reviewer->getKey(),
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);

            $post->save();

            $post->categories()->sync(
                collect($revision->categories ?? [])->map(fn ($id): int => (int) $id)->all(),
            );

            $post->tags()->sync(
                collect($revision->tags ?? [])
                    ->filter(fn ($name): bool => filled($name))
                    ->map(fn ($name): int => PostTag::findOrCreateByName((string) $name)->getKey())
                    ->all(),
            );

            $revision->update([
                'status' => PostRevision::STATUS_APPROVED,
                'reviewed_by_user_id' => $reviewer->getKey(),
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);

            return $post->refresh();
        });

        $post->loadMissing('user');
        $post->user?->notifyNow(new PostRevisionApprovedNotification($post));

        return $post;
    }

    /**
     * Kembalikan revisi ke penulis tanpa menyentuh artikel terbit.
     */
    public function reject(PostRevision $revision, User $reviewer, string $reason): PostRevision
    {
        $revision->update([
            'status' => PostRevision::STATUS_REJECTED,
            'reviewed_by_user_id' => $reviewer->getKey(),
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $revision->loadMissing(['post.user']);
        $post = $revision->post;

        if ($post?->user !== null) {
            $post->user->notifyNow(new PostRevisionRejectedNotification($post, $reason));
        }

        return $revision->refresh();
    }

    /**
     * @param  array<int, mixed>  $normalized
     */
    protected function relationDiffers(Post $post, string $relation, array $normalized): bool
    {
        if ($relation === 'categories') {
            $current = $post->categories()
                ->pluck('post_categories.id')
                ->map(fn ($id): int => (int) $id)
                ->sort()
                ->values()
                ->all();

            return $current !== collect($normalized)->map(fn ($id): int => (int) $id)->sort()->values()->all();
        }

        $current = $post->tags()
            ->pluck('name')
            ->map(fn ($name): string => (string) $name)
            ->sort()
            ->values()
            ->all();

        return $current !== collect($normalized)->map(fn ($name): string => (string) $name)->sort()->values()->all();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function contentDiffers(Post $post, array $data): bool
    {
        return (string) ($data['title'] ?? '') !== (string) $post->title
            || (string) ($data['slug'] ?? '') !== (string) $post->slug
            || (string) ($data['summary'] ?? '') !== (string) ($post->summary ?? '')
            || (string) ($data['content'] ?? '') !== (string) $post->content
            || (string) ($data['cover_image'] ?? '') !== (string) ($post->cover_image ?? '')
            || (bool) ($data['allow_comments'] ?? true) !== (bool) $post->allow_comments;
    }
}
