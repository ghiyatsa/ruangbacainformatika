<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Post;
use App\Models\SearchHistory;
use App\Models\Skripsi;
use App\Models\User;
use App\Services\Search\BookSearchRanker;
use App\Services\Search\SearchSuggestionBuilder;
use App\Services\Search\SearchTermResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        protected BookSearchRanker $bookRanker,
        protected SearchTermResolver $terms,
        protected SearchSuggestionBuilder $suggestionBuilder,
    ) {}

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

        $queryWords = $this->terms->words($q);

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
            ->tap(fn (Builder $query) => $this->bookRanker->apply($query, $q))
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
                'subtitle' => 'Artikel',
                'url' => route('posts.show', $p->slug),
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
            $this->suggestionBuilder->collectMultiField($queryWords, 6 - count($suggestions), $isMember),
        );

        // Koreksi typo jika saran masih kosong
        if (empty($suggestions)) {
            $corrected = $this->terms->correct($q);

            if ($corrected !== null) {
                $correctedWords = $this->terms->words($corrected);

                if (! empty($correctedWords)) {
                    $suggestions = array_merge(
                        $suggestions,
                        $this->suggestionBuilder->collectMultiField($correctedWords, 6, $isMember),
                    );
                }
            }
        }

        $formattedSuggestions = [];
        $seen = [];

        foreach ($suggestions as $suggestion) {
            $formatted = $this->suggestionBuilder->format($suggestion);
            $normalized = mb_strtolower($formatted);

            if (isset($seen[$normalized])) {
                continue;
            }

            $seen[$normalized] = true;
            $formattedSuggestions[] = $formatted;
        }

        $finalSuggestions = array_slice($formattedSuggestions, 0, 5);

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
}
