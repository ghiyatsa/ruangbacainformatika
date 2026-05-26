<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\InternshipReport;
use App\Models\Skripsi;
use App\Models\Thesis;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $search = str($request->string('q')->toString())
            ->squish()
            ->limit(100, '')
            ->toString();

        if ($search === '') {
            return response()->json([]);
        }

        $books = Book::query()
            ->published()
            ->search($search)
            ->select(['id', 'title', 'slug', 'cover_image'])
            ->with(['authors:id,name'])
            ->limit(5)
            ->get();

        $skripsis = Skripsi::query()
            ->search($search)
            ->select(['id', 'title', 'author_name', 'student_id'])
            ->tap(fn (Builder $query) => $this->applyAcademicSearchRanking($query, $search))
            ->limit(5)
            ->get();

        $internshipReports = InternshipReport::query()
            ->search($search)
            ->select(['id', 'title', 'author_name', 'student_id'])
            ->tap(fn (Builder $query) => $this->applyAcademicSearchRanking($query, $search))
            ->limit(5)
            ->get();

        $theses = Thesis::query()
            ->search($search)
            ->select(['id', 'title', 'author_name', 'student_id'])
            ->tap(fn (Builder $query) => $this->applyAcademicSearchRanking($query, $search))
            ->limit(5)
            ->get();

        return response()->json([
            'books' => $books
                ->map(fn (Book $book): array => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'slug' => $book->slug,
                    'coverImageUrl' => $book->cover_image
                        ? asset('storage/'.$book->cover_image)
                        : asset('images/book-cover-placeholder.svg'),
                    'authors' => $book->authors->pluck('name')->values()->all(),
                ])
                ->values()
                ->all(),
            'skripsis' => $skripsis
                ->map(fn (Skripsi $skripsi): array => [
                    'id' => $skripsi->id,
                    'title' => $skripsi->title,
                    'authorName' => $skripsi->author_name,
                    'studentId' => $skripsi->student_id,
                ])
                ->values()
                ->all(),
            'internshipReports' => $internshipReports
                ->map(fn (InternshipReport $internshipReport): array => [
                    'id' => $internshipReport->id,
                    'title' => $internshipReport->title,
                    'authorName' => $internshipReport->author_name,
                    'studentId' => $internshipReport->student_id,
                ])
                ->values()
                ->all(),
            'theses' => $theses
                ->map(fn (Thesis $thesis): array => [
                    'id' => $thesis->id,
                    'title' => $thesis->title,
                    'authorName' => $thesis->author_name,
                    'studentId' => $thesis->student_id,
                ])
                ->values()
                ->all(),
        ]);
    }

    protected function applyAcademicSearchRanking(Builder $query, string $search): void
    {
        $wildcardSearch = "%{$search}%";

        $query
            ->selectRaw(
                'CASE
                    WHEN title LIKE ? THEN 4
                    WHEN author_name LIKE ? THEN 3
                    WHEN student_id LIKE ? THEN 2
                    WHEN keywords LIKE ? THEN 1
                    WHEN abstract LIKE ? THEN 1
                    ELSE 0
                END as search_priority',
                [
                    $wildcardSearch,
                    $wildcardSearch,
                    $wildcardSearch,
                    $wildcardSearch,
                    $wildcardSearch,
                ]
            )
            ->orderByDesc('search_priority');

        if ($this->supportsAcademicFullText($query)) {
            $query
                ->selectRaw(
                    'MATCH(title, author_name, abstract, keywords) AGAINST (? IN BOOLEAN MODE) as search_relevance',
                    [$this->toBooleanFullTextQuery($search)]
                )
                ->orderByDesc('search_relevance');
        }

        $query->orderBy('title');
    }

    protected function supportsAcademicFullText(Builder $query): bool
    {
        return in_array(
            $query->getConnection()->getDriverName(),
            ['mysql', 'mariadb'],
            true
        );
    }

    protected function toBooleanFullTextQuery(string $search): string
    {
        return str($search)
            ->explode(' ')
            ->filter()
            ->map(fn (string $term): string => sprintf('%s*', $term))
            ->implode(' ');
    }
}
