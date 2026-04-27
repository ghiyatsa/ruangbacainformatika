<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Book;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    public function show(Book $book): Response
    {
        abort_if(! $book->is_published, 404);

        $book->load([
            'authors:id,name',
            'categories:id,name',
            'publisher:id,name',
        ])->loadCount([
            'items',
            'items as available_items_count' => fn ($query) => $query->available(),
        ]);

        return Inertia::render('books/show', [
            'book' => new BookResource($book),
        ]);
    }
}
