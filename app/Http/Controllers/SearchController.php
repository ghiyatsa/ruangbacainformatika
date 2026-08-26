<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\InternshipReport;
use App\Models\Post;
use App\Models\SearchHistory;
use App\Models\Skripsi;
use App\Models\Thesis;
use App\Services\Search\SearchTermCorrector;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SearchController extends Controller
{
    protected const RESULTS_PER_TYPE = 15;

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): InertiaResponse
    {
        $search = str($request->string('q')->toString())
            ->squish()
            ->limit(100, '')
            ->toString();

        $results = [
            'books' => collect(),
            'posts' => collect(),
            'skripsis' => collect(),
            'internshipReports' => collect(),
            'theses' => collect(),
        ];

        $totals = [
            'books' => null,
            'posts' => null,
            'skripsis' => null,
            'internshipReports' => null,
            'theses' => null,
        ];

        if ($search !== '') {
            $results = $this->performSearch($search);

            // Fallback koreksi typo: hanya saat hasil benar-benar kosong (tanpa biaya tambahan di jalur normal).
            if ($this->totalDisplayed($results) === 0) {
                $corrected = app(SearchTermCorrector::class)->correctQuery($search);

                if ($corrected !== null) {
                    $results = $this->mergeSearchResults($results, $this->performSearch($corrected));
                }
            }

            // Hitung total sebenarnya hanya jika hasil mentok limit (dipotong).
            $totals = [
                'books' => $results['books']->count() >= self::RESULTS_PER_TYPE ? Book::query()->published()->search($search)->count() : null,
                'posts' => $results['posts']->count() >= self::RESULTS_PER_TYPE ? Post::query()->published()->search($search)->count() : null,
                'skripsis' => $results['skripsis']->count() >= self::RESULTS_PER_TYPE ? Skripsi::query()->search($search)->count() : null,
                'internshipReports' => $results['internshipReports']->count() >= self::RESULTS_PER_TYPE ? InternshipReport::query()->search($search)->count() : null,
                'theses' => $results['theses']->count() >= self::RESULTS_PER_TYPE ? Thesis::query()->search($search)->count() : null,
            ];

            $hasResults = $this->totalDisplayed($results) > 0;

            if ($request->hasHeader('X-Search-Clicked') && $hasResults) {
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
            } elseif (mb_strlen($search) >= 2) {
                SearchHistory::query()->firstOrCreate(
                    ['query' => $search],
                    ['hits' => 1]
                );
            }
        }

        $payload = [
            'books' => $results['books']->values()->all(),
            'posts' => $results['posts']->values()->all(),
            'skripsis' => $results['skripsis']->values()->all(),
            'internshipReports' => $results['internshipReports']->values()->all(),
            'theses' => $results['theses']->values()->all(),
            'totals' => $totals,
        ];

        return Inertia::render('search/index', [
            'query' => $search,
            'resultsPerType' => self::RESULTS_PER_TYPE,
            'results' => app()->runningUnitTests() ? $payload : Inertia::defer(fn (): array => $payload),
        ]);
    }

    /**
     * Jalankan pencarian 5 tipe dan kembalikan koleksi hasil (sudah di-map ke array).
     *
     * @return array<string, Collection<int, array<string, mixed>>>
     */
    protected function performSearch(string $search): array
    {
        $books = Book::query()
            ->published()
            ->search($search)
            ->select(['books.id', 'books.title', 'books.slug', 'books.cover_image', 'books.is_featured', 'books.is_borrowable', 'books.view_count', 'books.published_year', 'books.pages', 'books.description'])
            ->with(['authors:id,name', 'categories:id,name,slug'])
            ->withCount([
                'items as available_items_count' => fn (Builder $query): Builder => $query->available(),
            ])
            ->limit(self::RESULTS_PER_TYPE)
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
            ->limit(self::RESULTS_PER_TYPE)
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
            ->limit(self::RESULTS_PER_TYPE)
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
            ->limit(self::RESULTS_PER_TYPE)
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
            ->limit(self::RESULTS_PER_TYPE)
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

        return [
            'books' => $books,
            'posts' => $posts,
            'skripsis' => $skripsis,
            'internshipReports' => $internshipReports,
            'theses' => $theses,
        ];
    }

    /**
     * @param  array<string, Collection<int, array<string, mixed>>>  $results
     */
    protected function totalDisplayed(array $results): int
    {
        return $results['books']->count()
            + $results['posts']->count()
            + $results['skripsis']->count()
            + $results['internshipReports']->count()
            + $results['theses']->count();
    }

    /**
     * Gabungkan dua kumpulan hasil, dedup berdasarkan id per tipe.
     *
     * @param  array<string, Collection<int, array<string, mixed>>>  $primary
     * @param  array<string, Collection<int, array<string, mixed>>>  $secondary
     * @return array<string, Collection<int, array<string, mixed>>>
     */
    protected function mergeSearchResults(array $primary, array $secondary): array
    {
        foreach ($primary as $key => $collection) {
            $primary[$key] = collect($collection)
                ->keyBy('id')
                ->merge(collect($secondary[$key])->keyBy('id'))
                ->values();
        }

        return $primary;
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

        $queryWords = collect(preg_split('/\s+/', mb_strtolower($q), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn (string $word): string => $this->sanitizeLikeTerm($word))
            ->filter()
            ->values()
            ->all();
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

        $suggestions = array_merge(
            $suggestions,
            $this->collectTitleSuggestions($queryWords, 8 - count($suggestions)),
        );

        // Koreksi typo: bila saran masih kurang, coba query terkoreksi.
        if (count($suggestions) < 8) {
            $corrected = app(SearchTermCorrector::class)->correctQuery($q);

            if ($corrected !== null) {
                $correctedWords = collect(preg_split('/\s+/', $corrected, -1, PREG_SPLIT_NO_EMPTY))
                    ->map(fn (string $word): string => $this->sanitizeLikeTerm($word))
                    ->filter()
                    ->values()
                    ->all();

                $needed = 8 - count($suggestions);

                if ($correctedWords !== []) {
                    $suggestions = array_merge($suggestions, SearchHistory::query()
                        ->where(function (Builder $inner) use ($correctedWords) {
                            foreach ($correctedWords as $word) {
                                $inner->where('query', 'like', "%{$word}%");
                            }
                        })
                        ->orderByDesc('hits')
                        ->limit($needed)
                        ->pluck('query')
                        ->all());
                }

                $suggestions = array_merge(
                    $suggestions,
                    $this->collectTitleSuggestions($correctedWords, 8 - count($suggestions)),
                );
            }
        }

        $formattedSuggestions = [];
        $seen = [];

        foreach ($suggestions as $suggestion) {
            $formatted = $this->formatSuggestion($suggestion, $q);
            $normalized = mb_strtolower($formatted);

            if (isset($seen[$normalized])) {
                continue;
            }

            $seen[$normalized] = true;
            $formattedSuggestions[] = $formatted;
        }
        $suggestions = array_slice(array_values($formattedSuggestions), 0, 8);

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
     * Buang wildcard LIKE yang bisa mengubah semantik pencarian.
     */
    protected function sanitizeLikeTerm(string $term): string
    {
        return str_replace(['\\', '%', '_'], '', $term);
    }

    /**
     * Kumpulkan judul yang cocok dengan seluruh kata, dari 5 tipe, hingga kuota terpenuhi.
     *
     * @param  list<string>  $words
     * @return list<string>
     */
    protected function collectTitleSuggestions(array $words, int $needed): array
    {
        if ($needed <= 0 || $words === []) {
            return [];
        }

        $titles = [];
        $remaining = $needed;

        $collectFrom = function (Builder $query) use (&$titles, &$remaining, $words): void {
            if ($remaining <= 0) {
                return;
            }

            $matched = $query
                ->where(function (Builder $inner) use ($words) {
                    foreach ($words as $word) {
                        $inner->where('title', 'like', "%{$word}%");
                    }
                })
                ->limit($remaining)
                ->pluck('title')
                ->all();

            $titles = array_merge($titles, $matched);
            $remaining -= count($matched);
        };

        $collectFrom(Book::query()->published());
        $collectFrom(Post::query()->published());
        $collectFrom(Skripsi::query());
        $collectFrom(Thesis::query());
        $collectFrom(InternshipReport::query());

        return $titles;
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
