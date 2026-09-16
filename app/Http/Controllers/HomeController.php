<?php

namespace App\Http\Controllers;

use App\Actions\Catalog\BuildHomeCatalogSections;
use App\Http\Resources\PostResource;
use App\Services\Post\PostQueryService;
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
        protected PostQueryService $postQueryService,
        protected PageMeta $pageMeta,
    ) {}

    public function __invoke(Request $request): Response
    {
        $paginated = $this->buildHomeCatalogSections->cachedFirstPageBooks();

        return Inertia::render('welcome/index', [
            'stats' => array_merge(
                $this->catalogService->getStats(),
                ['searchResultsCount' => $paginated['total'] ?? 0]
            ),
            'featuredBooks' => $this->buildHomeCatalogSections->featuredBooks(),
            'popularBooks' => $this->buildHomeCatalogSections->popularBooks(),
            'mostBorrowedBooks' => $this->buildHomeCatalogSections->mostBorrowedBooks(),
            'popularCategoryShelves' => $this->buildHomeCatalogSections->popularCategoryShelves(),
            'latestPosts' => PostResource::collection($this->postQueryService->latestForHome(4))->resolve(),
            'books' => $paginated,
        ])->withViewData([
            'meta' => $this->pageMeta->forWelcome(),
        ]);
    }
}
