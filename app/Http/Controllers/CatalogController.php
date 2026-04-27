<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Services\CatalogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
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

        $books = Book::query()
            ->published()
            ->search($search)
            ->forCategory($categorySlug)
            ->with(['authors:id,name', 'categories:id,name', 'publisher:id,name'])
            ->withCount([
                'items',
                'items as available_items_count' => fn ($query) => $query->available(),
            ])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_year')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('catalog', [
            'filters' => [
                'search' => $search,
                'category' => $categorySlug,
            ],
            'stats' => array_merge(
                $this->catalogService->getStats(),
                ['searchResultsCount' => $books->total()]
            ),
            'categories' => $this->catalogService->getCategoriesWithCounts()->all(),
            'books' => BookResource::collection($books),
        ]);
    }
}
