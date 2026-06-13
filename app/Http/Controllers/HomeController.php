<?php

namespace App\Http\Controllers;

use App\Actions\Catalog\BuildHomeCatalogSections;
use App\Http\Resources\BookCatalogResource;
use App\Services\CatalogService;
use App\Support\PageMeta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        protected BuildHomeCatalogSections $buildHomeCatalogSections,
        protected CatalogService $catalogService,
        protected PageMeta $pageMeta,
    ) {}

    public function __invoke(Request $request): Response
    {
        $books = $this->buildHomeCatalogSections->paginatedBooks();

        return Inertia::render('welcome/index', [
            'stats' => array_merge(
                $this->catalogService->getStats(),
                ['searchResultsCount' => $books->total()]
            ),
            'featuredBooks' => Inertia::optional(fn () => $this->buildHomeCatalogSections->featuredBooks()),
            'popularBooks' => Inertia::optional(fn () => $this->buildHomeCatalogSections->popularBooks()),
            'mostBorrowedBooks' => Inertia::optional(fn () => $this->buildHomeCatalogSections->mostBorrowedBooks()),
            'popularCategoryShelves' => Inertia::optional(
                fn () => $this->buildHomeCatalogSections->popularCategoryShelves()
            ),
            'books' => Inertia::optional(function () use ($books) {
                $paginated = $books->toArray();
                $paginated['data'] = BookCatalogResource::collection($books->getCollection())->resolve();

                return $paginated;
            }),
        ])->withViewData([
            'meta' => $this->pageMeta->forWelcome(),
        ]);
    }
}
