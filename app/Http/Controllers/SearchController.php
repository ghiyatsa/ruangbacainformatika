<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\InternshipReport;
use App\Models\Post;
use App\Models\SearchHistory;
use App\Models\Skripsi;
use App\Models\Thesis;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SearchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): InertiaResponse
    {
        $search = str($request->string('q')->toString())
            ->squish()
            ->limit(100, '')
            ->toString();

        $books = collect();
        $posts = collect();
        $skripsis = collect();
        $internshipReports = collect();
        $theses = collect();

        if ($search !== '') {
            // Track search history
            SearchHistory::query()->upsert(
                [['query' => $search, 'hits' => 1]],
                ['query'],
                ['hits' => DB::raw('search_histories.hits + 1')]
            );

            $books = Book::query()
                ->published()
                ->search($search)
                ->select(['books.id', 'books.title', 'books.slug', 'books.cover_image', 'books.is_featured', 'books.is_borrowable', 'books.view_count', 'books.published_year', 'books.pages', 'books.description'])
                ->with(['authors:id,name', 'categories:id,name,slug'])
                ->withCount([
                    'items as available_items_count' => fn (Builder $query): Builder => $query->available(),
                ])
                ->limit(15)
                ->get()
                ->map(fn (Book $book): array => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'slug' => $book->slug,
                    'coverImageUrl' => $book->cover_image
                        ? asset('storage/'.$book->cover_image)
                        : asset('images/book-cover-placeholder.svg'),
                    'authors' => $book->authors->pluck('name')->values()->all(),
                    'categories' => $book->categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug])->all(),
                    'isFeatured' => $book->is_featured,
                    'isBorrowable' => $book->is_borrowable,
                    'isAvailable' => $book->is_borrowable && ($book->available_items_count ?? 0) > 0,
                    'viewCount' => $book->view_count,
                    'publishedYear' => $book->published_year,
                    'pages' => $book->pages,
                    'shortDescription' => Str::limit($book->description ?: 'Deskripsi buku belum tersedia.', 160),
                ]);

            $posts = Post::query()
                ->published()
                ->search($search)
                ->select(['posts.id', 'posts.title', 'posts.slug', 'posts.summary', 'posts.cover_image', 'posts.user_id', 'posts.published_at'])
                ->with(['user:id,name', 'categories:id,name,slug'])
                ->limit(15)
                ->get()
                ->map(fn (Post $post): array => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'coverImageUrl' => $post->cover_image
                        ? asset('storage/'.$post->cover_image)
                        : asset('images/book-cover-placeholder.svg'),
                    'author' => $post->user ? ['name' => $post->user->name] : null,
                    'summary' => $post->summary,
                    'excerpt' => $post->excerpt(120),
                    'categories' => $post->categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug])->all(),
                    'publishedAt' => $post->published_at?->toIso8601String(),
                    'publishedAtLabel' => $post->published_at?->translatedFormat('d F Y'),
                ]);

            $skripsis = Skripsi::query()
                ->search($search)
                ->select(['id', 'title', 'author_name', 'student_id', 'year', 'keywords'])
                ->tap(fn (Builder $query) => $this->applyAcademicSearchRanking($query, $search))
                ->limit(15)
                ->get()
                ->map(fn (Skripsi $skripsi): array => [
                    'id' => $skripsi->id,
                    'title' => $skripsi->title,
                    'authorName' => $skripsi->author_name,
                    'studentId' => $skripsi->student_id,
                    'year' => $skripsi->year,
                    'keywords' => filled($skripsi->keywords)
                        ? array_map('trim', explode(',', $skripsi->keywords))
                        : [],
                ]);

            $internshipReports = InternshipReport::query()
                ->search($search)
                ->select(['id', 'title', 'author_name', 'student_id', 'year', 'keywords'])
                ->tap(fn (Builder $query) => $this->applyAcademicSearchRanking($query, $search))
                ->limit(15)
                ->get()
                ->map(fn (InternshipReport $internshipReport): array => [
                    'id' => $internshipReport->id,
                    'title' => $internshipReport->title,
                    'authorName' => $internshipReport->author_name,
                    'studentId' => $internshipReport->student_id,
                    'year' => $internshipReport->year,
                    'keywords' => filled($internshipReport->keywords)
                        ? array_map('trim', explode(',', $internshipReport->keywords))
                        : [],
                ]);

            $theses = Thesis::query()
                ->search($search)
                ->select(['id', 'title', 'author_name', 'student_id', 'year', 'keywords'])
                ->tap(fn (Builder $query) => $this->applyAcademicSearchRanking($query, $search))
                ->limit(15)
                ->get()
                ->map(fn (Thesis $thesis): array => [
                    'id' => $thesis->id,
                    'title' => $thesis->title,
                    'authorName' => $thesis->author_name,
                    'studentId' => $thesis->student_id,
                    'year' => $thesis->year,
                    'keywords' => filled($thesis->keywords)
                        ? array_map('trim', explode(',', $thesis->keywords))
                        : [],
                ]);
        }

        return Inertia::render('search/index', [
            'query' => $search,
            'results' => app()->runningUnitTests()
                ? [
                    'books' => $books->values()->all(),
                    'posts' => $posts->values()->all(),
                    'skripsis' => $skripsis->values()->all(),
                    'internshipReports' => $internshipReports->values()->all(),
                    'theses' => $theses->values()->all(),
                ]
                : Inertia::defer(fn () => [
                    'books' => $books->values()->all(),
                    'posts' => $posts->values()->all(),
                    'skripsis' => $skripsis->values()->all(),
                    'internshipReports' => $internshipReports->values()->all(),
                    'theses' => $theses->values()->all(),
                ]),
        ]);
    }

    /**
     * Get list of search suggestions.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $q = str($request->string('q')->toString())
            ->squish()
            ->limit(100, '')
            ->toString();

        if ($q === '') {
            return response()->json([]);
        }

        $suggestions = SearchHistory::query()
            ->where('query', 'like', "{$q}%")
            ->orWhere('query', 'like', "% {$q}%")
            ->orderByDesc('hits')
            ->limit(8)
            ->pluck('query')
            ->all();

        // Fallback: If suggestions count is less than 8, get matching titles from Books, Posts, Skripsi, Thesis
        $needed = 8 - count($suggestions);
        if ($needed > 0) {
            $bookTitles = Book::query()
                ->published()
                ->where('title', 'like', "%{$q}%")
                ->limit($needed)
                ->pluck('title')
                ->all();
            $suggestions = array_merge($suggestions, $bookTitles);
        }

        $needed = 8 - count($suggestions);
        if ($needed > 0) {
            $postTitles = Post::query()
                ->published()
                ->where('title', 'like', "%{$q}%")
                ->limit($needed)
                ->pluck('title')
                ->all();
            $suggestions = array_merge($suggestions, $postTitles);
        }

        $needed = 8 - count($suggestions);
        if ($needed > 0) {
            $skripsiTitles = Skripsi::query()
                ->where('title', 'like', "%{$q}%")
                ->limit($needed)
                ->pluck('title')
                ->all();
            $suggestions = array_merge($suggestions, $skripsiTitles);
        }

        $needed = 8 - count($suggestions);
        if ($needed > 0) {
            $thesisTitles = Thesis::query()
                ->where('title', 'like', "%{$q}%")
                ->limit($needed)
                ->pluck('title')
                ->all();
            $suggestions = array_merge($suggestions, $thesisTitles);
        }

        $suggestions = array_slice(array_values(array_unique($suggestions)), 0, 8);

        return response()->json($suggestions);
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
