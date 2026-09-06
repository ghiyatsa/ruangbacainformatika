<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Post;
use App\Models\SearchHistory;
use App\Models\Skripsi;
use App\Models\User;
use App\Services\Search\SearchTermCorrector;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Get list of search suggestions or quick spotlight results.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $q = str($request->string('q')->toString())
            ->squish()
            ->limit(100, '')
            ->toString();

        $isLegacy = $request->boolean('legacy');

        if ($q === '') {
            return response()->json($isLegacy ? [] : [
                'suggestions' => [],
                'quickResults' => [],
            ]);
        }

        $queryWords = collect(preg_split('/\s+/', mb_strtolower($q), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn (string $word): string => $this->sanitizeLikeTerm($word))
            ->filter()
            ->values()
            ->all();

        if (empty($queryWords)) {
            return response()->json($isLegacy ? [] : [
                'suggestions' => [],
                'quickResults' => [],
            ]);
        }

        $user = $request->user();
        $isMember = $user instanceof User && $user->hasRole('member');

        // 1. Quick Items (Buku, Skripsi, Artikel) yang cocok langsung dengan ranking relevansi
        $quickBooks = Book::query()
            ->published()
            ->search($q)
            ->select(['books.id', 'books.title', 'books.slug', 'books.view_count'])
            ->tap(fn (Builder $query) => $this->applyBookSearchRanking($query, $q))
            ->with(['authors:id,name', 'categories:id,name,slug'])
            ->limit(5)
            ->get()
            ->map(fn (Book $b): array => [
                'type' => 'book',
                'id' => $b->id,
                'title' => $b->title,
                'subtitle' => $b->authors->pluck('name')->join(', ') ?: 'Penulis tidak tersedia',
                'url' => route('books.show', ['book' => $b->slug ?: (string) $b->id]),
            ]);

        $quickSkripsi = $isMember
            ? Skripsi::query()
                ->search($q)
                ->select(['id', 'title', 'author_name', 'student_id'])
                ->tap(fn (Builder $query) => $this->applyAcademicSearchRanking($query, $q))
                ->limit(3)
                ->get()
                ->map(fn (Skripsi $s): array => [
                    'type' => 'skripsi',
                    'id' => $s->id,
                    'title' => $s->title,
                    'subtitle' => "{$s->author_name} ({$s->student_id})",
                    'url' => route('skripsi.show', $s->student_id),
                ])
            : collect();

        $quickPosts = Post::query()
            ->published()
            ->search($q)
            ->limit(3)
            ->get()
            ->map(fn (Post $p): array => [
                'type' => 'post',
                'id' => $p->id,
                'title' => $p->title,
                'subtitle' => 'Artikel Blog',
                'url' => route('blog.show', $p->slug),
            ]);

        // 2. Teks Saran Pintar (Query suggestions)
        $suggestions = SearchHistory::query()
            ->where(function (Builder $inner) use ($queryWords) {
                foreach ($queryWords as $word) {
                    $inner->where('query', 'like', "%{$word}%");
                }
            })
            ->orderByDesc('hits')
            ->limit(4)
            ->pluck('query')
            ->all();

        $suggestions = array_merge(
            $suggestions,
            $this->collectMultiFieldSuggestions($queryWords, 6 - count($suggestions), $isMember),
        );

        // Koreksi typo jika saran masih kosong
        if (empty($suggestions)) {
            $corrected = app(SearchTermCorrector::class)->correctQuery($q);

            if ($corrected !== null) {
                $correctedWords = collect(preg_split('/\s+/', $corrected, -1, PREG_SPLIT_NO_EMPTY))
                    ->map(fn (string $word): string => $this->sanitizeLikeTerm($word))
                    ->filter()
                    ->values()
                    ->all();

                if (! empty($correctedWords)) {
                    $suggestions = array_merge(
                        $suggestions,
                        $this->collectMultiFieldSuggestions($correctedWords, 6, $isMember),
                    );
                }
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

        $finalSuggestions = array_slice(array_values($formattedSuggestions), 0, 5);

        // Jika request dari klien lama yang hanya menerima array string biasa:
        if ($request->boolean('legacy')) {
            return response()->json($finalSuggestions);
        }

        return response()->json([
            'suggestions' => $finalSuggestions,
            'quickResults' => [
                'books' => $quickBooks->all(),
                'skripsi' => $quickSkripsi->all(),
                'posts' => $quickPosts->all(),
            ],
        ]);
    }

    /**
     * Apply field-priority ordering for book search results.
     *
     * Exact title / prefix title > author > publisher / category > description.
     */
    protected function applyBookSearchRanking(Builder $query, string $search): void
    {
        $exact = $search;
        $prefix = "{$search}%";
        $wildcard = "%{$search}%";

        $query
            ->selectRaw(
                'CASE
                    WHEN books.title = ? THEN 100
                    WHEN books.title LIKE ? THEN 80
                    WHEN books.title LIKE ? THEN 60
                    WHEN books.subtitle LIKE ? THEN 40
                    WHEN books.isbn LIKE ? OR books.issn LIKE ? OR books.ddc_code LIKE ? THEN 30
                    WHEN books.description LIKE ? THEN 10
                    ELSE 5
                END as search_priority',
                [
                    $exact,
                    $prefix,
                    $wildcard,
                    $wildcard,
                    $wildcard,
                    $wildcard,
                    $wildcard,
                    $wildcard,
                ]
            )
            ->orderByDesc('search_priority')
            ->orderByDesc('books.view_count')
            ->orderBy('books.title');
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
        $exact = $search;
        $prefix = "{$search}%";
        $wildcard = "%{$search}%";

        $query
            ->selectRaw(
                'CASE
                    WHEN title = ? THEN 100
                    WHEN title LIKE ? THEN 80
                    WHEN title LIKE ? THEN 60
                    WHEN author_name LIKE ? THEN 40
                    WHEN student_id LIKE ? THEN 30
                    WHEN keywords LIKE ? THEN 20
                    WHEN abstract LIKE ? THEN 10
                    ELSE 0
                END as search_priority',
                [
                    $exact,
                    $prefix,
                    $wildcard,
                    $wildcard,
                    $wildcard,
                    $wildcard,
                    $wildcard,
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
     * Kumpulkan saran teks berbasis frasa pencarian organik (Google/Gramedia style).
     *
     * @param  list<string>  $words
     * @return list<string>
     */
    protected function collectMultiFieldSuggestions(array $words, int $needed, bool $includeAcademic = true): array
    {
        if ($needed <= 0 || $words === []) {
            return [];
        }

        $results = [];
        $remaining = $needed;
        $prefix = implode(' ', $words);

        // 1. Kategori / Topik yang cocok (misal: "Kecerdasan Buatan", "Pemrograman Web")
        $categories = Category::query()
            ->where(function (Builder $query) use ($words) {
                foreach ($words as $word) {
                    $query->where('name', 'like', "%{$word}%");
                }
            })
            ->limit($remaining)
            ->pluck('name')
            ->all();

        foreach ($categories as $cat) {
            $results[] = mb_strtolower($cat);
            $remaining--;
        }

        // 2. Pengarang / Dosen / Author Langsung
        if ($remaining > 0) {
            $authors = Author::query()
                ->where(function (Builder $query) use ($words) {
                    foreach ($words as $word) {
                        $query->where('name', 'like', "%{$word}%");
                    }
                })
                ->limit($remaining)
                ->pluck('name')
                ->all();

            foreach ($authors as $author) {
                $results[] = mb_strtolower($author);
                $remaining--;
            }
        }

        // 3. Ekstrak frasa 2-4 kata dari judul buku yang relevan (bukan seluruh judul panjang)
        if ($remaining > 0) {
            $titles = Book::query()
                ->published()
                ->where(function (Builder $query) use ($words) {
                    foreach ($words as $word) {
                        $query->where('title', 'like', "%{$word}%");
                    }
                })
                ->limit(10)
                ->pluck('title')
                ->all();

            foreach ($titles as $title) {
                if ($remaining <= 0) {
                    break;
                }

                $phrase = $this->extractMeaningfulPhrase($title, $words);
                if ($phrase !== null && ! in_array($phrase, $results, true)) {
                    $results[] = $phrase;
                    $remaining--;
                }
            }
        }

        // 4. Kata Kunci Skripsi / Karya Ilmiah
        if ($includeAcademic && $remaining > 0) {
            $keywordsList = Skripsi::query()
                ->whereNotNull('keywords')
                ->where(function (Builder $query) use ($words) {
                    foreach ($words as $word) {
                        $query->where('keywords', 'like', "%{$word}%");
                    }
                })
                ->limit(10)
                ->pluck('keywords')
                ->all();

            foreach ($keywordsList as $kwString) {
                if ($remaining <= 0) {
                    break;
                }

                $items = array_map('trim', explode(',', $kwString));
                foreach ($items as $item) {
                    $itemLower = mb_strtolower($item);
                    $matchesAll = true;
                    foreach ($words as $w) {
                        if (! str_contains($itemLower, $w)) {
                            $matchesAll = false;
                            break;
                        }
                    }

                    if ($matchesAll && mb_strlen($itemLower) >= 3 && ! in_array($itemLower, $results, true)) {
                        $results[] = $itemLower;
                        $remaining--;
                        if ($remaining <= 0) {
                            break;
                        }
                    }
                }
            }
        }

        return array_values(array_unique(array_filter($results)));
    }

    /**
     * Potong judul panjang menjadi frasa query organik atau judul yang bersih.
     *
     * @param  list<string>  $words
     */
    protected function extractMeaningfulPhrase(string $title, array $words): ?string
    {
        $clean = preg_replace('/[^\p{L}\p{N}\s\-\–]/u', '', mb_strtolower($title));

        if ($clean === null || trim($clean) === '') {
            return null;
        }

        $tokens = preg_split('/\s+/', trim($clean), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        // Bila judul <= 8 kata, gunakan seluruh judul bersih agar tetap bermakna penuh
        if (count($tokens) <= 8) {
            return $clean;
        }

        // Untuk judul sangat panjang, potong 4-5 kata di sekitar kata kunci
        $targetIndex = 0;
        foreach ($tokens as $idx => $token) {
            foreach ($words as $w) {
                if (str_contains($token, $w)) {
                    $targetIndex = $idx;
                    break 2;
                }
            }
        }

        $start = max(0, $targetIndex - 1);
        $slice = array_slice($tokens, $start, 5);

        return implode(' ', $slice);
    }

    /**
     * Format a search suggestion like Google Autocomplete.
     */
    protected function formatSuggestion(string $text, string $query): string
    {
        $textLower = mb_strtolower($text);
        // Remove special characters except letters, numbers, spaces, and hyphens
        $textClean = preg_replace('/[^\p{L}\p{N}\s\-\–]/u', '', $textLower);

        $queryLower = mb_strtolower($query);
        $queryWords = preg_split('/\s+/', $queryLower, -1, PREG_SPLIT_NO_EMPTY);
        if (empty($queryWords)) {
            return $textClean;
        }

        return $textClean;
    }
}
