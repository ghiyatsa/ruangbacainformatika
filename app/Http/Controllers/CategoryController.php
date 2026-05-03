<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\Category;
use App\Services\CatalogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(
        protected CatalogService $catalogService,
    ) {}

    public function show(Request $request, Category $category): Response
    {
        $search = str($request->string('search')->toString())
            ->squish()
            ->limit(100, '')
            ->toString();

        $books = Book::query()
            ->published()
            ->search($search)
            ->forCategory($category->slug)
            ->with(['authors:id,name', 'categories:id,name', 'publisher:id,name'])
            ->withCount([
                'items',
                'items as available_items_count' => fn ($query) => $query->available(),
            ])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_year')
            ->orderBy('title')
            ->paginate(24)
            ->withQueryString();

        return Inertia::render('books/category', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
            ],
            'filters' => [
                'search' => $search,
            ],
            'stats' => array_merge(
                $this->catalogService->getStats(),
                ['searchResultsCount' => $books->total()]
            ),
            'categories' => $this->catalogService->getCategoriesWithCounts()->all(),
            'books' => Inertia::defer(fn () => BookResource::collection($books)),
        ]);
    }
}
