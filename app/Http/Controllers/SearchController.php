<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Http\Resources\SkripsiResource;
use App\Models\Book;
use App\Models\Skripsi;
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

        $books = Book::query()
            ->published()
            ->search($search)
            ->with(['authors:id,name', 'categories:id,name'])
            ->limit(5)
            ->get();

        $skripsis = Skripsi::query()
            ->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author_name', 'like', "%{$search}%");
            })
            ->limit(5)
            ->get();

        return response()->json([
            'books' => BookResource::collection($books)->resolve(),
            'skripsis' => SkripsiResource::collection($skripsis)->resolve(),
        ]);
    }
}
