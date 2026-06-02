<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookCatalogResource;
use App\Models\Book;
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
            'categories' => $this->catalogService->getHighlightCategories(12)->all(),
            'marqueeCategories' => Inertia::defer(
                fn () => $this->catalogService->getHighlightCategories(24)->all(),
                'marquee'
            ),
            'featuredBooks' => Inertia::defer(fn () => $this->featuredBooks()),
            'popularBooks' => Inertia::defer(fn () => $this->popularBooks()),
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
}
