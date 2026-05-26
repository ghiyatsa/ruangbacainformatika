<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Services\CatalogService;
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
        'subtitle',
        'slug',
        'isbn',
        'issn',
        'description',
        'cover_image',
        'published_year',
        'pages',
        'language',
        'is_featured',
        'is_borrowable',
        'view_count',
        'publisher_id',
    ];

    public function __construct(
        protected CatalogService $catalogService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $books = Book::query()
            ->published()
            ->select(self::BOOK_LIST_COLUMNS)
            ->with(['authors:id,name', 'categories:id,name', 'publisher:id,name'])
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
            'categories' => $this->catalogService->getCategoriesWithCounts()->all(),
            'featuredBooks' => Inertia::defer(fn () => $this->featuredBooks()),
            'popularBooks' => Inertia::defer(fn () => $this->popularBooks()),
            'books' => Inertia::defer(function () use ($books) {
                $paginated = $books->toArray();
                $paginated['data'] = BookResource::collection($books->getCollection())->resolve();

                return $paginated;
            }),
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
            ->with(['authors:id,name', 'categories:id,name', 'publisher:id,name'])
            ->withCount([
                'items',
                'items as available_items_count' => fn ($query) => $query->available(),
            ])
            ->limit(5)
            ->get();

        return BookResource::collection($books)->resolve();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function popularBooks(): array
    {
        $books = Book::query()
            ->published()
            ->select(self::BOOK_LIST_COLUMNS)
            ->with(['authors:id,name', 'categories:id,name', 'publisher:id,name'])
            ->withCount([
                'items',
                'items as available_items_count' => fn ($query) => $query->available(),
            ])
            ->orderByDesc('view_count')
            ->orderBy('title')
            ->limit(6)
            ->get();

        return BookResource::collection($books)->resolve();
    }
}
