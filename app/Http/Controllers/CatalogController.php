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
        $year = $request->integer('year') ?: null;
        $featured = $request->boolean('featured');
        $availability = $request->boolean('availability');

        $booksQuery = Book::query()
            ->published()
            ->search($search)
            ->forCategory($categorySlug)
            ->forYear($year)
            ->when($featured, fn ($q) => $q->featured())
            ->onlyAvailable($availability)
            ->with(['authors:id,name', 'categories:id,name', 'publisher:id,name'])
            ->withCount([
                'items',
                'items as available_items_count' => fn ($query) => $query->available(),
            ])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_year')
            ->orderBy('title');

        $searchResultsCount = (clone $booksQuery)->count();

        return Inertia::render('catalog', [
            'filters' => [
                'search' => $search,
                'category' => $categorySlug,
                'year' => $year,
                'featured' => $featured,
                'availability' => $availability,
            ],
            'stats' => array_merge(
                $this->catalogService->getStats(),
                ['searchResultsCount' => $searchResultsCount]
            ),
            'years' => Book::published()
                ->whereNotNull('published_year')
                ->distinct()
                ->orderByDesc('published_year')
                ->pluck('published_year')
                ->all(),
            'categories' => $this->catalogService->getCategoriesWithCounts()->all(),
            'books' => Inertia::defer(function () use ($booksQuery) {
                $books = (clone $booksQuery)
                    ->paginate(24)
                    ->withQueryString();

                $paginated = $books->toArray();
                $paginated['data'] = BookResource::collection($books->getCollection())->resolve();

                return $paginated;
            })->merge()->append('data'),
        ]);
    }
}
