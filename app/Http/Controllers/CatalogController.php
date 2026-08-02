<?php

namespace App\Http\Controllers;

use App\Services\Catalog\CatalogPageCache;
use App\Services\Catalog\CatalogQueryService;
use App\Services\CatalogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function __construct(
        protected CatalogPageCache $catalogPageCache,
        protected CatalogQueryService $catalogQueryService,
        protected CatalogService $catalogService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $filters = $this->catalogQueryService->filtersFromRequest($request);
        $page = max($request->integer('page', 1), 1);

        return Inertia::render('books/index', [
            'filters' => $filters,
            'stats' => array_merge(
                $this->catalogService->getStats(),
                ['searchResultsCount' => $this->catalogPageCache->searchResultsCount($filters)]
            ),
            'activeFilterLabels' => $this->catalogQueryService->activeFilterLabels($filters),
            'years' => Inertia::defer(fn () => $this->catalogPageCache->years(), 'catalog-filters'),
            'categories' => Inertia::defer(
                fn () => $this->catalogPageCache->categories(),
                'catalog-filters',
            ),
            'authors' => Inertia::defer(fn () => $this->catalogPageCache->authors(), 'catalog-filters'),
            'publishers' => Inertia::defer(fn () => $this->catalogPageCache->publishers(), 'catalog-filters'),
            'books' => Inertia::defer(function () use ($filters, $page) {
                return $this->catalogPageCache->booksIndex($filters, $page, 12);
            }, 'books')->merge()->append('data'),
        ]);
    }
}
