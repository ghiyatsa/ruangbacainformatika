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
                'items as available_items_count' => fn ($query) => $query
                    ->available()
                    ->whereHas('book', fn ($bookQuery) => $bookQuery->where('is_borrowable', true)),
            ])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_year')
            ->orderBy('title')
            ->paginate(4);

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
            'featuredBook' => $this->featuredBook(),
            'books' => BookResource::collection($books),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function featuredBook(): ?array
    {
        $book = Book::query()
            ->published()
            ->featured()
            ->with(['authors:id,name', 'categories:id,name', 'publisher:id,name'])
            ->withCount([
                'items',
                'items as available_items_count' => fn ($query) => $query
                    ->available()
                    ->whereHas('book', fn ($bookQuery) => $bookQuery->where('is_borrowable', true)),
            ])
            ->first();

        if ($book === null) {
            return null;
        }

        return (new BookResource($book))->toArray(request());
    }
}
