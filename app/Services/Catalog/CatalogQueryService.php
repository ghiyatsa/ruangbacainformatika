<?php

namespace App\Services\Catalog;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CatalogQueryService
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
     * @return array{
     *     search: string,
     *     category: string,
     *     author: string,
     *     publisher: string,
     *     year: int|null,
     *     featured: bool,
     *     availability: bool
     * }
     */
    public function filtersFromRequest(Request $request): array
    {
        return [
            'search' => str($request->string('search')->toString())
                ->squish()
                ->limit(100, '')
                ->toString(),
            'category' => $request->string('category')->toString(),
            'author' => $request->string('author')->toString(),
            'publisher' => $request->string('publisher')->toString(),
            'year' => $request->integer('year') ?: null,
            'featured' => $request->boolean('featured'),
            'availability' => $request->boolean('availability'),
        ];
    }

    /**
     * @param  array{search: string, category: string, author: string, publisher: string, year: int|null, featured: bool, availability: bool}  $filters
     * @return Builder<Book>
     */
    public function booksQuery(array $filters): Builder
    {
        return Book::query()
            ->published()
            ->search($filters['search'])
            ->forCategory($filters['category'])
            ->forAuthor($filters['author'])
            ->forPublisher($filters['publisher'])
            ->forYear($filters['year'])
            ->when($filters['featured'], fn ($query) => $query->featured())
            ->onlyAvailable($filters['availability'])
            ->select(self::BOOK_LIST_COLUMNS)
            ->with(['authors:id,name', 'categories:id,name,slug'])
            ->withCount([
                'items',
                'items as available_items_count' => fn ($query) => $query->available(),
            ])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_year')
            ->orderBy('title');
    }

    /**
     * @return array<int, int>
     */
    public function years(): array
    {
        return Book::published()
            ->whereNotNull('published_year')
            ->distinct()
            ->orderByDesc('published_year')
            ->pluck('published_year')
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string, booksCount: int}>
     */
    public function authors(): array
    {
        return Author::query()
            ->select(['id', 'name', 'slug'])
            ->whereHas('books', fn ($query) => $query->published())
            ->withCount([
                'books' => fn ($query) => $query->published(),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (Author $author) => [
                'id' => $author->id,
                'name' => $author->name,
                'slug' => $author->slug,
                'booksCount' => $author->books_count,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string, booksCount: int}>
     */
    public function publishers(): array
    {
        return Publisher::query()
            ->select(['id', 'name', 'slug'])
            ->whereHas('books', fn ($query) => $query->published())
            ->withCount([
                'books' => fn ($query) => $query->published(),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (Publisher $publisher) => [
                'id' => $publisher->id,
                'name' => $publisher->name,
                'slug' => $publisher->slug,
                'booksCount' => $publisher->books_count,
            ])
            ->all();
    }

    /**
     * @param  array{category: string, author: string, publisher: string}  $filters
     * @return array{category: string|null, author: string|null, publisher: string|null}
     */
    public function activeFilterLabels(array $filters): array
    {
        return [
            'category' => $this->resolveCategoryLabel($filters['category']),
            'author' => $this->resolveAuthorLabel($filters['author']),
            'publisher' => $this->resolvePublisherLabel($filters['publisher']),
        ];
    }

    protected function resolveCategoryLabel(string $categorySlug): ?string
    {
        if ($categorySlug === '') {
            return null;
        }

        return Category::query()
            ->where('slug', $categorySlug)
            ->value('name');
    }

    protected function resolveAuthorLabel(string $authorSlug): ?string
    {
        if ($authorSlug === '') {
            return null;
        }

        return Author::query()
            ->where('slug', $authorSlug)
            ->value('name');
    }

    protected function resolvePublisherLabel(string $publisherSlug): ?string
    {
        if ($publisherSlug === '') {
            return null;
        }

        return Publisher::query()
            ->where('slug', $publisherSlug)
            ->value('name');
    }
}
