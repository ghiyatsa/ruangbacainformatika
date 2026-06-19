<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/** @mixin Post */
class BlogPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'excerpt' => $this->excerpt(),
            'content' => $this->content,
            'contentText' => Str::of(strip_tags((string) $this->content))->squish()->toString(),
            'coverImageUrl' => $this->cover_image
                ? asset('storage/'.$this->cover_image)
                : asset('images/article-placeholder.svg'),
            'status' => $this->status,
            'publishedAt' => $this->published_at?->toIso8601String(),
            'publishedAtLabel' => $this->published_at?->translatedFormat('d M Y'),
            'reviewedAt' => $this->reviewed_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'updatedAtLabel' => $this->updated_at?->translatedFormat('d M Y'),
            'viewCount' => (int) $this->view_count,
            'readingMinutes' => max(1, (int) ceil(str_word_count(strip_tags((string) $this->content)) / 200)),
            'author' => $this->whenLoaded('user', fn (): array => [
                'name' => $this->user->name,
                'avatar' => $this->user->avatarUrl(),
            ]),
            'reviewer' => $this->whenLoaded('reviewedBy', fn (): ?array => $this->reviewedBy ? [
                'name' => $this->reviewedBy->name,
            ] : null),
            'categories' => $this->whenLoaded('categories', fn () => $this->categories
                ->map(fn ($category): array => [
                    'name' => $category->name,
                    'slug' => $category->slug,
                ])
                ->values()
                ->all()),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags
                ->map(fn ($tag): array => [
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ])
                ->values()
                ->all()),
        ];
    }
}
