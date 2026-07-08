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
                ->with(['user:id,name,avatar_url', 'categories:id,name,slug'])
                ->limit(15)
                ->get()
                ->map(fn (Post $post): array => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'coverImageUrl' => $post->cover_image
                        ? asset('storage/'.$post->cover_image)
                        : asset('images/book-cover-placeholder.svg'),
                    'author' => $post->user ? [
                        'name' => $post->user->name,
                        'avatar' => $post->user->avatarUrl(),
                        'initials' => $post->user->initials(),
                    ] : null,
                    'summary' => $post->summary,
                    'excerpt' => $post->excerpt(120),
                    'categories' => $post->categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug])->all(),
                    'publishedAt' => $post->published_at?->toIso8601String(),
                    'publishedAtLabel' => $post->published_at?->translatedFormat('d F Y'),
                ]);

            $skripsis = Skripsi::query()
                ->search($search)
                ->select(['id', 'title', 'author_name', 'student_id', 'year', 'keywords', 'abstract', 'view_count'])
                ->tap(fn (Builder $query) => $this->applyAcademicSearchRanking($query, $search))
                ->limit(15)
                ->get()
                ->map(fn (Skripsi $skripsi): array => [
                    'id' => $skripsi->id,
                    'title' => $skripsi->title,
                    'authorName' => $skripsi->author_name,
                    'studentId' => $skripsi->student_id,
                    'year' => $skripsi->year,
                    'abstract' => $skripsi->abstract,
                    'viewCount' => (int) $skripsi->view_count,
                    'keywords' => filled($skripsi->keywords)
                        ? array_map('trim', explode(',', $skripsi->keywords))
                        : [],
                ]);

            $internshipReports = InternshipReport::query()
                ->search($search)
                ->select(['id', 'title', 'author_name', 'student_id', 'year', 'keywords', 'abstract', 'view_count'])
                ->tap(fn (Builder $query) => $this->applyAcademicSearchRanking($query, $search))
                ->limit(15)
                ->get()
                ->map(fn (InternshipReport $internshipReport): array => [
                    'id' => $internshipReport->id,
                    'title' => $internshipReport->title,
                    'authorName' => $internshipReport->author_name,
                    'studentId' => $internshipReport->student_id,
                    'year' => $internshipReport->year,
                    'abstract' => $internshipReport->abstract,
                    'viewCount' => (int) $internshipReport->view_count,
                    'keywords' => filled($internshipReport->keywords)
                        ? array_map('trim', explode(',', $internshipReport->keywords))
                        : [],
                ]);

            $theses = Thesis::query()
                ->search($search)
                ->select(['id', 'title', 'author_name', 'student_id', 'year', 'keywords', 'abstract', 'view_count'])
                ->tap(fn (Builder $query) => $this->applyAcademicSearchRanking($query, $search))
                ->limit(15)
                ->get()
                ->map(fn (Thesis $thesis): array => [
                    'id' => $thesis->id,
                    'title' => $thesis->title,
                    'authorName' => $thesis->author_name,
                    'studentId' => $thesis->student_id,
                    'year' => $thesis->year,
                    'abstract' => $thesis->abstract,
                    'viewCount' => (int) $thesis->view_count,
                    'keywords' => filled($thesis->keywords)
                        ? array_map('trim', explode(',', $thesis->keywords))
                        : [],
                ]);

            $hasResults = $books->isNotEmpty() || $posts->isNotEmpty() || $skripsis->isNotEmpty() || $internshipReports->isNotEmpty() || $theses->isNotEmpty();

            if ($hasResults) {
                if ($request->hasHeader('X-Search-Clicked')) {
                    // Cache-based deduplication: prevent the same IP from inflating
                    // the hit counter for the same query within a 5-minute window.
                    $cacheKey = 'search_hit_'.sha1($request->ip().'|'.$search);

                    if (! cache()->has($cacheKey)) {
                        cache()->put($cacheKey, true, now()->addMinutes(5));
                        SearchHistory::query()->upsert(
                            [['query' => $search, 'hits' => 1]],
                            ['query'],
                            ['hits' => DB::raw('search_histories.hits + 1')]
                        );
                    }
                } else {
                    SearchHistory::query()->firstOrCreate(
                        ['query' => $search],
                        ['hits' => 1]
                    );
                }
            }
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

        $queryWords = preg_split('/\s+/', mb_strtolower($q), -1, PREG_SPLIT_NO_EMPTY);
        if (empty($queryWords)) {
            return response()->json([]);
        }

        $suggestions = SearchHistory::query()
            ->where(function (Builder $inner) use ($queryWords) {
                foreach ($queryWords as $word) {
                    $inner->where('query', 'like', "%{$word}%");
                }
            })
            ->orderByDesc('hits')
            ->limit(8)
            ->pluck('query')
            ->all();

        // Fallback: If suggestions count is less than 8, get matching titles from Books, Posts, Skripsi, Thesis
        $needed = 8 - count($suggestions);
        if ($needed > 0) {
            $bookTitles = Book::query()
                ->published()
                ->where(function (Builder $inner) use ($queryWords) {
                    foreach ($queryWords as $word) {
                        $inner->where('title', 'like', "%{$word}%");
                    }
                })
                ->limit($needed)
                ->pluck('title')
                ->all();
            $suggestions = array_merge($suggestions, $bookTitles);
        }

        $needed = 8 - count($suggestions);
        if ($needed > 0) {
            $postTitles = Post::query()
                ->published()
                ->where(function (Builder $inner) use ($queryWords) {
                    foreach ($queryWords as $word) {
                        $inner->where('title', 'like', "%{$word}%");
                    }
                })
                ->limit($needed)
                ->pluck('title')
                ->all();
            $suggestions = array_merge($suggestions, $postTitles);
        }

        $needed = 8 - count($suggestions);
        if ($needed > 0) {
            $skripsiTitles = Skripsi::query()
                ->where(function (Builder $inner) use ($queryWords) {
                    foreach ($queryWords as $word) {
                        $inner->where('title', 'like', "%{$word}%");
                    }
                })
                ->limit($needed)
                ->pluck('title')
                ->all();
            $suggestions = array_merge($suggestions, $skripsiTitles);
        }

        $needed = 8 - count($suggestions);
        if ($needed > 0) {
            $thesisTitles = Thesis::query()
                ->where(function (Builder $inner) use ($queryWords) {
                    foreach ($queryWords as $word) {
                        $inner->where('title', 'like', "%{$word}%");
                    }
                })
                ->limit($needed)
                ->pluck('title')
                ->all();
            $suggestions = array_merge($suggestions, $thesisTitles);
        }

        $needed = 8 - count($suggestions);
        if ($needed > 0) {
            $internshipReportTitles = InternshipReport::query()
                ->where(function (Builder $inner) use ($queryWords) {
                    foreach ($queryWords as $word) {
                        $inner->where('title', 'like', "%{$word}%");
                    }
                })
                ->limit($needed)
                ->pluck('title')
                ->all();
            $suggestions = array_merge($suggestions, $internshipReportTitles);
        }

        $formattedSuggestions = [];
        foreach ($suggestions as $suggestion) {
            $formattedSuggestions[] = $this->formatSuggestion($suggestion, $q);
        }
        $suggestions = array_slice(array_values(array_unique($formattedSuggestions)), 0, 8);

        return response()->json($suggestions);
    }

    /**
     * Apply field-priority ordering for academic search results.
     *
     * Uses a CASE-based score (title > author > student_id > keywords/abstract)
     * for deterministic ordering. The full-text relevance score from
     * scopeSearch (WHERE clause) already handles the filtering; adding a
     * second MATCH in SELECT would evaluate the FTS index twice unnecessarily.
     */
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
            ->orderByDesc('search_priority')
            ->orderBy('title');
    }

    /**
     * Format a search suggestion like Google Autocomplete.
     */
    protected function formatSuggestion(string $text, string $query): string
    {
        $textLower = mb_strtolower($text);
        // Remove special characters except letters, numbers, spaces, and hyphens
        $textClean = preg_replace('/[^\p{L}\p{N}\s\-]/u', '', $textLower);

        $queryLower = mb_strtolower($query);
        $queryWords = preg_split('/\s+/', $queryLower, -1, PREG_SPLIT_NO_EMPTY);
        if (empty($queryWords)) {
            return $textClean;
        }

        $words = preg_split('/\s+/', $textClean, -1, PREG_SPLIT_NO_EMPTY);
        if ($words === false) {
            return $textClean;
        }

        $firstMatchIndex = -1;
        $lastMatchIndex = -1;

        foreach ($words as $index => $word) {
            foreach ($queryWords as $qWord) {
                if (mb_strpos($word, $qWord) !== false) {
                    if ($firstMatchIndex === -1) {
                        $firstMatchIndex = $index;
                    }
                    $lastMatchIndex = $index;
                }
            }
        }

        if ($firstMatchIndex !== -1 && $lastMatchIndex !== -1) {
            // Take from the first match to the last match, plus 3 words after the last match
            $length = ($lastMatchIndex - $firstMatchIndex) + 4;
            $slice = array_slice($words, $firstMatchIndex, $length);

            return implode(' ', $slice);
        }

        return $textClean;
    }
}
