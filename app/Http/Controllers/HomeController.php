<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookCatalogResource;
use App\Models\Book;
use App\Models\Category;
use App\Services\CatalogService;
use App\Support\PageMeta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
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
        protected PageMeta $pageMeta,
    ) {}

    public function __invoke(Request $request): Response
    {
        $books = Book::query()
            ->published()
            ->select(self::BOOK_LIST_COLUMNS)
            ->with(['authors:id,name', 'categories:id,name,slug'])
            ->withCount([
                'items',
                'items as available_items_count' => fn ($query) => $query->available(),
            ])
            ->latest()
            ->orderByDesc('id')
            ->orderBy('title')
            ->paginate(12);

        return Inertia::render('welcome', [
            'stats' => array_merge(
                $this->catalogService->getStats(),
                ['searchResultsCount' => $books->total()]
            ),
            'featuredBooks' => Inertia::defer(fn () => $this->featuredBooks()),
            'popularBooks' => Inertia::defer(fn () => $this->popularBooks()),
            'mostBorrowedBooks' => Inertia::defer(fn () => $this->mostBorrowedBooks()),
            'popularCategoryShelves' => Inertia::defer(
                fn () => $this->popularCategoryShelves(),
                'popular-category-shelves'
            ),
            'books' => Inertia::defer(function () use ($books) {
                $paginated = $books->toArray();
                $paginated['data'] = BookCatalogResource::collection($books->getCollection())->resolve();

                return $paginated;
            }),
        ])->withViewData([
            'meta' => $this->pageMeta->forWelcome(),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function featuredBooks(): array
    {
        $books = Book::query()
            ->published()
            ->featured()
            ->select(self::BOOK_LIST_COLUMNS)
            ->with(['authors:id,name', 'categories:id,name,slug'])
            ->withCount([
                'items',
                'items as available_items_count' => fn ($query) => $query->available(),
            ])
            ->limit(5)
            ->get();

        return BookCatalogResource::collection($books)->resolve();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function popularBooks(): array
    {
        $books = Book::query()
            ->published()
            ->select(self::BOOK_LIST_COLUMNS)
            ->with(['authors:id,name', 'categories:id,name,slug'])
            ->withCount([
                'items',
                'items as available_items_count' => fn ($query) => $query->available(),
            ])
            ->orderByDesc('view_count')
            ->orderBy('title')
            ->limit(6)
            ->get();

        return BookCatalogResource::collection($books)->resolve();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function mostBorrowedBooks(): array
    {
        $books = Book::query()
            ->published()
            ->where('is_borrowable', true)
            ->select(self::BOOK_LIST_COLUMNS)
            ->withCount([
                'loanItems as borrow_count',
                'items',
                'items as available_items_count' => fn ($query) => $query->available(),
            ])
            ->with(['authors:id,name', 'categories:id,name,slug'])
            ->having('borrow_count', '>', 0)
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
    protected function popularCategoryShelves(): array
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
                $books = Book::query()
                    ->published()
                    ->whereHas('categories', fn ($query) => $query->whereKey($category->id))
                    ->select(self::BOOK_LIST_COLUMNS)
                    ->with(['authors:id,name', 'categories:id,name,slug'])
                    ->withCount([
                        'items',
                        'items as available_items_count' => fn ($query) => $query->available(),
                    ])
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
}
