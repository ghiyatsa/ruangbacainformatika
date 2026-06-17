<?php

namespace App\Services\Blog;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BlogQueryService
{
    /**
     * @return array{search: string, category: string, tag: string}
     */
    public function filtersFromRequest(Request $request): array
    {
        return [
            'search' => $request->string('search')->trim()->toString(),
            'category' => $request->string('category')->trim()->toString(),
            'tag' => $request->string('tag')->trim()->toString(),
        ];
    }

    /**
     * @param  array{search: string, category: string, tag: string}  $filters
     */
    public function postsQuery(array $filters): Builder
    {
        return Post::query()
            ->published()
            ->with([
                'user:id,name,avatar_url',
                'categories:id,name,slug',
                'tags:id,name,slug',
            ])
            ->search($filters['search'])
            ->forCategory($filters['category'])
            ->forTag($filters['tag'])
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    /**
     * @param  array{search: string, category: string, tag: string}  $filters
     */
    public function paginatePosts(array $filters, int $perPage = 9): LengthAwarePaginator
    {
        return $this->postsQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string, postsCount: int}>
     */
    public function categories(): array
    {
        return PostCategory::query()
            ->whereHas('posts', fn (Builder $query): Builder => $query->published())
            ->withCount([
                'posts as posts_count' => fn (Builder $query): Builder => $query->published(),
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (PostCategory $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'postsCount' => (int) $category->posts_count,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string, postsCount: int}>
     */
    public function tags(): array
    {
        return PostTag::query()
            ->whereHas('posts', fn (Builder $query): Builder => $query->published())
            ->withCount([
                'posts as posts_count' => fn (Builder $query): Builder => $query->published(),
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (PostTag $tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'postsCount' => (int) $tag->posts_count,
            ])
            ->all();
    }

    /**
     * @param  array{search: string, category: string, tag: string}  $filters
     * @return array<int, array{key: string, label: string}>
     */
    public function activeFilterLabels(array $filters): array
    {
        $labels = [];

        if ($filters['category'] !== '') {
            $category = PostCategory::query()
                ->where('slug', $filters['category'])
                ->value('name');

            if ($category !== null) {
                $labels[] = [
                    'key' => 'category',
                    'label' => $category,
                ];
            }
        }

        if ($filters['tag'] !== '') {
            $tag = PostTag::query()
                ->where('slug', $filters['tag'])
                ->value('name');

            if ($tag !== null) {
                $labels[] = [
                    'key' => 'tag',
                    'label' => $tag,
                ];
            }
        }

        if ($filters['search'] !== '') {
            $labels[] = [
                'key' => 'search',
                'label' => $filters['search'],
            ];
        }

        return $labels;
    }

    /**
     * @return array<int, Post>
     */
    public function latestForHome(int $limit = 3): array
    {
        return Post::query()
            ->published()
            ->with(['user:id,name,avatar_url', 'categories:id,name,slug', 'tags:id,name,slug'])
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get()
            ->all();
    }

    /**
     * @return array<int, Post>
     */
    public function relatedPosts(Post $post, int $limit = 3): array
    {
        $categoryIds = $post->categories->pluck('id')->all();
        $tagIds = $post->tags->pluck('id')->all();

        return Post::query()
            ->published()
            ->whereKeyNot($post->getKey())
            ->with(['user:id,name,avatar_url', 'categories:id,name,slug', 'tags:id,name,slug'])
            ->when(
                $categoryIds !== [] || $tagIds !== [],
                function (Builder $query) use ($categoryIds, $tagIds): void {
                    $query->where(function (Builder $relatedQuery) use ($categoryIds, $tagIds): void {
                        if ($categoryIds !== []) {
                            $relatedQuery->whereHas('categories', fn (Builder $categoryQuery): Builder => $categoryQuery->whereIn('post_categories.id', $categoryIds));
                        }

                        if ($tagIds !== []) {
                            if ($categoryIds !== []) {
                                $relatedQuery->orWhereHas('tags', fn (Builder $tagQuery): Builder => $tagQuery->whereIn('post_tags.id', $tagIds));
                            } else {
                                $relatedQuery->whereHas('tags', fn (Builder $tagQuery): Builder => $tagQuery->whereIn('post_tags.id', $tagIds));
                            }
                        }
                    });
                },
            )
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get()
            ->all();
    }
}
