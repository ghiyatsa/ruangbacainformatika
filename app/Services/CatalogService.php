<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Category;
use Illuminate\Support\Collection;

class CatalogService
{
    /**
     * Get shared catalog statistics.
     *
     * @return array{
     *     booksCount: int,
     *     featuredCount: int,
     *     availableItemsCount: int,
     *     activeCategoriesCount: int
     * }
     */
    public function getStats(): array
    {
        $bookStats = Book::query()
            ->published()
            ->selectRaw('COUNT(*) as books_count')
            ->selectRaw('SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured_count')
            ->first();

        return [
            'booksCount' => (int) ($bookStats?->books_count ?? 0),
            'featuredCount' => (int) ($bookStats?->featured_count ?? 0),
            'availableItemsCount' => BookItem::query()
                ->available()
                ->join('books', 'books.id', '=', 'book_items.book_id')
                ->where('books.is_published', true)
                ->where('books.is_borrowable', true)
                ->count(),
            'activeCategoriesCount' => Category::query()
                ->whereHas('books', fn ($query) => $query->published())
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
            ->select(['id', 'name', 'slug', 'description'])
            ->withCount([
                'books as books_count' => fn ($query) => $query->published(),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'booksCount' => (int) ($category->books_count ?? 0),
            ]);
    }
}
