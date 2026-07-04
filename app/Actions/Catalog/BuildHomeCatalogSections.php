<?php

namespace App\Actions\Catalog;

use App\Http\Resources\BookCatalogResource;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class BuildHomeCatalogSections
{
    /**
     * @var array<int, string>
     */
    protected const BOOK_LIST_COLUMNS = [
        'id',
        'title',
        'slug',
        'description',
        'cover_image',
        'published_year',
        'pages',
        'is_featured',
        'is_borrowable',
        'view_count',
    ];

    /**
     * @return LengthAwarePaginator<int, Book>
     */
    public function paginatedBooks(): LengthAwarePaginator
    {
        return $this->bookQuery()
            ->orderByRaw('CASE WHEN cover_image IS NOT NULL THEN 0 ELSE 1 END')
            ->latest()
            ->orderByDesc('id')
            ->orderBy('title')
            ->paginate(12);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function featuredBooks(): array
    {
        $books = $this->bookQuery()
            ->featured()
            ->limit(5)
            ->get();

        return BookCatalogResource::collection($books)->resolve();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function popularBooks(): array
    {
        $books = $this->bookQuery()
            ->orderByDesc('view_count')
            ->orderBy('title')
            ->limit(6)
            ->get();

        return BookCatalogResource::collection($books)->resolve();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function mostBorrowedBooks(): array
    {
        $books = $this->bookQuery()
            ->where('is_borrowable', true)
            ->withCount([
                'loanItems as borrow_count',
            ])
            ->has('loanItems')
            ->orderByDesc('borrow_count')
            ->orderByDesc('view_count')
            ->orderBy('title')
            ->limit(6)
            ->get();

        return BookCatalogResource::collection($books)->resolve();
    }

    /**
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     slug: string,
     *     description: string|null,
     *     booksCount: int,
     *     books: array<int, array<string, mixed>>
     * }>
     */
    public function popularCategoryShelves(): array
    {
        return Category::query()
            ->select(['id', 'name', 'slug', 'description'])
            ->whereHas('books', fn ($query) => $query->published())
            ->withCount([
                'books as books_count' => fn ($query) => $query->published(),
            ])
            ->orderByDesc('books_count')
            ->orderBy('name')
            ->limit(3)
            ->get()
            ->map(function (Category $category): array {
                $books = $this->bookQuery()
                    ->whereHas('categories', fn ($query) => $query->whereKey($category->id))
                    ->orderByDesc('view_count')
                    ->orderByDesc('published_year')
                    ->orderBy('title')
                    ->limit(6)
                    ->get();

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'booksCount' => (int) ($category->books_count ?? 0),
                    'books' => BookCatalogResource::collection($books)->resolve(),
                ];
            })
            ->all();
    }

    /**
     * @return Builder<Book>
     */
    protected function bookQuery(): Builder
    {
        return Book::query()
            ->published()
            ->select(self::BOOK_LIST_COLUMNS)
            ->with(['authors:id,name', 'categories:id,name,slug'])
            ->withCount([
                'items',
                'items as available_items_count' => fn (Builder $query): Builder => $query->available(),
            ]);
    }
}
