<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Http\Resources\InternshipReportResource;
use App\Http\Resources\SkripsiResource;
use App\Http\Resources\ThesisResource;
use App\Models\Book;
use App\Models\InternshipReport;
use App\Models\Skripsi;
use App\Models\Thesis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $search = $request->string('q')->trim()->toString();

        if ($search === '') {
            return response()->json([]);
        }

        $books = Book::query()
            ->published()
            ->search($search)
            ->with(['authors:id,name', 'categories:id,name'])
            ->limit(5)
            ->get();

        $skripsis = Skripsi::query()
            ->search($search)
            ->limit(5)
            ->get();

        $internshipReports = InternshipReport::query()
            ->search($search)
            ->limit(5)
            ->get();

        $theses = Thesis::query()
            ->search($search)
            ->limit(5)
            ->get();

        return response()->json([
            'books' => BookResource::collection($books)->resolve(),
            'skripsis' => SkripsiResource::collection($skripsis)->resolve(),
            'internshipReports' => InternshipReportResource::collection($internshipReports)->resolve(),
            'theses' => ThesisResource::collection($theses)->resolve(),
        ]);
    }
}
