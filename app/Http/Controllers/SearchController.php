<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $search = $request->string('q')->trim()->toString();

        if (empty($search)) {
            return response()->json([]);
        }

        $books = \App\Models\Book::query()
            ->published()
            ->search($search)
            ->with(['authors:id,name', 'categories:id,name'])
            ->limit(8)
            ->get();

        return \App\Http\Resources\BookResource::collection($books);
    }
}
