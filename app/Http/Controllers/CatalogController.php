<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookCatalogResource;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Services\CatalogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
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

    public function __construct(
        protected CatalogService $catalogService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $search = str($request->string('search')->toString())
            ->squish()
            ->limit(100, '')
            ->toString();

        $categorySlug = $request->string('category')->toString();
        $authorSlug = $request->string('author')->toString();
        $publisherSlug = $request->string('publisher')->toString();
        $year = $request->integer('year') ?: null;
        $featured = $request->boolean('featured');
        $availability = $request->boolean('availability');

        $booksQuery = Book::query()
            ->published()
            ->search($search)
            ->forCategory($categorySlug)
            ->forAuthor($authorSlug)
            ->forPublisher($publisherSlug)
            ->forYear($year)
            ->when($featured, fn ($q) => $q->featured())
            ->onlyAvailable($availability)
            ->select(self::BOOK_LIST_COLUMNS)
            ->with(['authors:id,name', 'categories:id,name,slug'])
            ->withCount([
                'items',
                'items as available_items_count' => fn ($query) => $query->available(),
            ])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_year')
            ->orderBy('title');

        $searchResultsCount = (clone $booksQuery)->count();

        return Inertia::render('catalog', [
            'filters' => [
                'search' => $search,
                'category' => $categorySlug,
                'author' => $authorSlug,
                'publisher' => $publisherSlug,
                'year' => $year,
                'featured' => $featured,
                'availability' => $availability,
            ],
            'stats' => array_merge(
                $this->catalogService->getStats(),
                ['searchResultsCount' => $searchResultsCount]
            ),
            'activeFilterLabels' => [
                'category' => $this->resolveCategoryLabel($categorySlug),
                'author' => $this->resolveAuthorLabel($authorSlug),
                'publisher' => $this->resolvePublisherLabel($publisherSlug),
            ],
            'years' => Inertia::defer(fn () => Book::published()
                ->whereNotNull('published_year')
                ->distinct()
                ->orderByDesc('published_year')
                ->pluck('published_year')
                ->all(), 'catalog-filters'),
            'categories' => Inertia::defer(
                fn () => $this->catalogService->getCategoriesWithCounts()->all(),
                'catalog-filters',
            ),
            'authors' => Inertia::defer(fn () => Author::query()
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
                ->all(), 'catalog-filters'),
            'publishers' => Inertia::defer(fn () => Publisher::query()
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
                ->all(), 'catalog-filters'),
            'books' => Inertia::defer(function () use ($booksQuery) {
                $books = (clone $booksQuery)
                    ->paginate(12)
                    ->withQueryString();

                $paginated = $books->toArray();
                $paginated['data'] = BookCatalogResource::collection($books->getCollection())->resolve();

                return $paginated;
            })->merge()->append('data'),
        ]);
    }

    private function resolveCategoryLabel(string $categorySlug): ?string
    {
        if ($categorySlug === '') {
            return null;
        }

        return Category::query()
            ->where('slug', $categorySlug)
            ->value('name');
    }

    private function resolveAuthorLabel(string $authorSlug): ?string
    {
        if ($authorSlug === '') {
            return null;
        }

        return Author::query()
            ->where('slug', $authorSlug)
            ->value('name');
    }

    private function resolvePublisherLabel(string $publisherSlug): ?string
    {
        if ($publisherSlug === '') {
            return null;
        }

        return Publisher::query()
            ->where('slug', $publisherSlug)
            ->value('name');
    }
}
