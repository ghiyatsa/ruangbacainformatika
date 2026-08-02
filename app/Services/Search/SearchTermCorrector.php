<?php

namespace App\Services\Search;

use App\Models\Book;
use App\Models\InternshipReport;
use App\Models\Post;
use App\Models\SearchHistory;
use App\Models\Skripsi;
use App\Models\Thesis;
use Illuminate\Support\Facades\Cache;

class SearchTermCorrector
{
    protected const DICTIONARY_CACHE_KEY = 'search:dictionary';

    protected const DICTIONARY_TTL_SECONDS = 21600;

    protected const DICTIONARY_MAX_TOKENS = 1500;

    /**
     * Kembalikan query terkoreksi (typo), atau null bila tidak ada perbaikan.
     */
    public function correctQuery(string $search): ?string
    {
        $terms = preg_split('/\s+/', mb_strtolower($search), -1, PREG_SPLIT_NO_EMPTY);

        if ($terms === false || $terms === []) {
            return null;
        }

        $dictionary = $this->buildDictionary();
        $changed = false;
        $corrected = [];

        foreach ($terms as $term) {
            $fixed = $this->correctTerm($term, $dictionary);
            $corrected[] = $fixed;

            if ($fixed !== $term) {
                $changed = true;
            }
        }

        return $changed ? implode(' ', $corrected) : null;
    }

    /**
     * @return list<string>
     */
    public function buildDictionary(): array
    {
        return Cache::remember(self::DICTIONARY_CACHE_KEY, self::DICTIONARY_TTL_SECONDS, function (): array {
            $seen = [];

            foreach (Book::query()->published()->pluck('title') as $title) {
                $this->collectTokens((string) $title, $seen);
            }
            foreach (Skripsi::query()->whereNotNull('keywords')->pluck('keywords') as $keywords) {
                $this->collectTokens((string) $keywords, $seen);
            }
            foreach (Thesis::query()->whereNotNull('keywords')->pluck('keywords') as $keywords) {
                $this->collectTokens((string) $keywords, $seen);
            }
            foreach (InternshipReport::query()->whereNotNull('keywords')->pluck('keywords') as $keywords) {
                $this->collectTokens((string) $keywords, $seen);
            }
            foreach (Post::query()->published()->pluck('title') as $title) {
                $this->collectTokens((string) $title, $seen);
            }
            foreach (SearchHistory::query()->pluck('query') as $query) {
                $this->collectTokens((string) $query, $seen);
            }

            $dictionary = array_keys($seen);
            sort($dictionary);

            return array_slice($dictionary, 0, self::DICTIONARY_MAX_TOKENS);
        });
    }

    /**
     * @param  array<string, true>  $seen
     */
    protected function collectTokens(string $text, array &$seen): void
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);

        if ($words === false) {
            return;
        }

        foreach ($words as $word) {
            if (mb_strlen($word) >= 3) {
                $seen[$word] = true;
            }
        }
    }

    /**
     * @param  list<string>  $dictionary
     */
    protected function correctTerm(string $term, array $dictionary): string
    {
        $length = mb_strlen($term);

        if ($length < 4) {
            return $term;
        }

        if (in_array($term, $dictionary, true)) {
            return $term;
        }

        $maxDistance = $length >= 6 ? 2 : 1;
        $best = $term;
        $bestDistance = $maxDistance + 1;

        foreach ($dictionary as $word) {
            $distance = levenshtein($term, $word);

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $best = $word;
            }
        }

        return $bestDistance <= $maxDistance ? $best : $term;
    }
}
