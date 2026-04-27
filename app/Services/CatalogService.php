<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CatalogService
{
    /**
     * Get shared catalog statistics.
     *
     * @return array{
     *     booksCount: int,
     *     featuredCount: int,
     *     availableItemsCount: int
     * }
     */
    public function getStats(): array
    {
        return [
            'booksCount' => Book::query()->published()->count(),
            'featuredCount' => Book::query()->published()->featured()->count(),
            'availableItemsCount' => BookItem::query()
                ->available()
                ->whereHas('book', fn (Builder $query): Builder => $query->published())
                ->count(),
        ];
    }

    /**
     * Get categories with their book counts.
     *
     * @return Collection<int, array{id: int, name: string, slug: string, booksCount: int}>
     */
    public function getCategoriesWithCounts(): Collection
    {
        return Category::query()
            ->withCount([
                'books as books_count' => fn (Builder $query): Builder => $query->published(),
            ])
            ->select(['id', 'name', 'slug'])
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'booksCount' => (int) ($category->books_count ?? 0),
            ]);
    }
}
