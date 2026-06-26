<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Services\LoanDraftService;
use App\Services\RelatedCatalogService;
use App\Support\PageMeta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    public function __construct(
        protected LoanDraftService $loanDraftService,
        protected RelatedCatalogService $relatedCatalogService,
        protected PageMeta $pageMeta,
    ) {}

    public function show(Request $request, Book $book): Response
    {
        abort_if(! $book->is_published, 404);

        $book->increment('view_count', 1);

        $book->load([
            'authors:id,name,slug',
            'categories:id,name,slug',
            'publisher:id,name,slug',
            'items' => fn ($query) => $query
                ->select(['id', 'book_id', 'status', 'shelf_location'])
                ->orderBy('id'),
        ])->loadCount([
            'items',
            'items as available_items_count' => fn ($query) => $query->available(),
        ]);

        return Inertia::render('books/show', [
            'book' => new BookResource($book),
            'loanRequest' => $request->user()?->canBorrowBooks()
                ? $this->loanDraftService->summaryForBook($request->user(), $book)
                : null,
            'relatedBooks' => Inertia::defer(
                fn () => BookResource::collection($this->relatedCatalogService->forBook($book))->resolve(),
                rescue: true,
            ),
        ])->withViewData([
            'meta' => $this->pageMeta->forBook($book),
        ]);
    }
}
