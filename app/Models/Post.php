<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    use GeneratesSlug;

    /** @use HasFactory<PostFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'summary',
        'content',
        'cover_image',
        'is_published',
        'published_at',
        'view_count',
        'status',
        'reviewed_by_user_id',
        'reviewed_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'view_count' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post): void {
            if ($post->status === self::STATUS_APPROVED) {
                $post->is_published = true;
                if (blank($post->published_at)) {
                    $post->published_at = now();
                }
            } else {
                $post->is_published = false;
                $post->published_at = null;
            }
        });

        static::deleted(function (Post $post): void {
            if ($post->cover_image && Storage::disk('public')->exists($post->cover_image)) {
                Storage::disk('public')->delete($post->cover_image);
            }
        });

        static::updating(function (Post $post): void {
            if (! $post->isDirty('cover_image')) {
                return;
            }

            $oldImage = $post->getOriginal('cover_image');

            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
        });
    }

    protected static function slugSourceAttribute(): string
    {
        return 'title';
    }

    protected static function slugFallbackValue(): string
    {
        return 'artikel';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(PostCategory::class, 'post_category_post');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(PostTag::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        if ($search === '') {
            return $query;
        }

        $terms = collect(explode(' ', $search))
            ->map(fn (string $term): string => trim($term))
            ->filter()
            ->values();

        return $query->where(function (Builder $searchQuery) use ($terms): void {
            foreach ($terms as $term) {
                $searchQuery->where(function (Builder $termQuery) use ($term): void {
                    $termQuery
                        ->where('title', 'like', "%{$term}%")
                        ->orWhere('summary', 'like', "%{$term}%")
                        ->orWhere('content', 'like', "%{$term}%")
                        ->orWhereHas('user', fn (Builder $userQuery): Builder => $userQuery->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('categories', fn (Builder $categoryQuery): Builder => $categoryQuery->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('tags', fn (Builder $tagQuery): Builder => $tagQuery->where('name', 'like', "%{$term}%"));
                });
            }
        });
    }

    public function scopeForCategory(Builder $query, string $categorySlug): Builder
    {
        if ($categorySlug === '') {
            return $query;
        }

        return $query->whereHas(
            'categories',
            fn (Builder $categoryQuery): Builder => $categoryQuery->where('slug', $categorySlug),
        );
    }

    public function scopeForTag(Builder $query, string $tagSlug): Builder
    {
        if ($tagSlug === '') {
            return $query;
        }

        return $query->whereHas(
            'tags',
            fn (Builder $tagQuery): Builder => $tagQuery->where('slug', $tagSlug),
        );
    }

    public function excerpt(int $limit = 180): string
    {
        $source = $this->summary ?: strip_tags((string) $this->content);

        return Str::of($source)
            ->squish()
            ->limit($limit)
            ->toString();
    }
}
