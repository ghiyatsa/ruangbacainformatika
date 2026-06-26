<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookCatalogResource;
use App\Services\Catalog\CatalogQueryService;
use App\Services\CatalogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function __construct(
        protected CatalogQueryService $catalogQueryService,
        protected CatalogService $catalogService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $filters = $this->catalogQueryService->filtersFromRequest($request);
        $booksQuery = $this->catalogQueryService->booksQuery($filters);

        $searchResultsCount = (clone $booksQuery)->count();

        return Inertia::render('books/index', [
            'filters' => $filters,
            'stats' => array_merge(
                $this->catalogService->getStats(),
                ['searchResultsCount' => $searchResultsCount]
            ),
            'activeFilterLabels' => $this->catalogQueryService->activeFilterLabels($filters),
            'years' => Inertia::defer(fn () => $this->catalogQueryService->years(), 'catalog-filters'),
            'categories' => Inertia::defer(
                fn () => $this->catalogService->getCategoriesWithCounts()->all(),
                'catalog-filters',
            ),
            'authors' => Inertia::defer(fn () => $this->catalogQueryService->authors(), 'catalog-filters'),
            'publishers' => Inertia::defer(fn () => $this->catalogQueryService->publishers(), 'catalog-filters'),
            'books' => Inertia::defer(function () use ($booksQuery) {
                $books = (clone $booksQuery)
                    ->paginate(12)
                    ->withQueryString();

                $paginated = $books->toArray();
                $paginated['data'] = BookCatalogResource::collection($books->getCollection())->resolve();

                return $paginated;
            }, 'books')->merge()->append('data'),
        ]);
    }
}
