<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Services\CatalogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class HomeController extends Controller
{
    public function __construct(
        protected CatalogService $catalogService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $books = Book::query()
            ->published()
            ->with(['authors:id,name', 'categories:id,name', 'publisher:id,name'])
            ->withCount([
                'items',
                'items as available_items_count' => fn ($query) => $query->available(),
            ])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_year')
            ->orderBy('title')
            ->paginate(10);

        return Inertia::render('welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'filters' => [
                'search' => '',
            ],
            'stats' => array_merge(
                $this->catalogService->getStats(),
                ['searchResultsCount' => $books->total()]
            ),
            'categories' => $this->catalogService->getCategoriesWithCounts()->all(),
            'featuredBooks' => Inertia::defer(fn () => $this->featuredBooks()),
            'books' => Inertia::defer(fn () => BookResource::collection($books)),
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
            ->with(['authors:id,name', 'categories:id,name', 'publisher:id,name'])
            ->withCount([
                'items',
                'items as available_items_count' => fn ($query) => $query->available(),
            ])
            ->limit(5)
            ->get();

        return BookResource::collection($books)->resolve();
    }
}
