<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Services\RelatedCatalogService;
use App\Support\PageMeta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    public function __construct(
        protected RelatedCatalogService $relatedCatalogService,
        protected PageMeta $pageMeta,
    ) {}

    public function show(Request $request, Book $book): Response
    {
        abort_if(! $book->is_published, 404);

        if (! $request->prefetch() && ! $request->hasHeader('X-Inertia-Partial-Component')) {
            $book->increment('view_count', 1);
        }

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
            // Buku terkait mengecualikan buku rekomendasi agar tidak ada judul
            // yang tampil dua kali di halaman yang sama.
            'relatedBooks' => Inertia::defer(
                fn () => BookResource::collection(
                    $this->relatedCatalogService->forBook(
                        $book,
                        excludeBookIds: $this->relatedCatalogService->recommendedForBook($book)->modelKeys(),
                    ),
                )->resolve(),
                rescue: true,
            ),
            'recommendedBooks' => Inertia::defer(
                fn () => BookResource::collection(
                    $this->relatedCatalogService->recommendedForBook($book),
                )->resolve(),
                rescue: true,
            ),
        ])->withViewData([
            'meta' => $this->pageMeta->forBook($book),
        ]);
    }
}
