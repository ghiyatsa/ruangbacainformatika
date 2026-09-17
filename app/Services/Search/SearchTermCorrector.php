<?php

namespace App\Services\Search;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
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

    /**
     * Batas kata kamus. Judul katalog saja menghasilkan lebih dari dua
     * ribu kata unik, sehingga batas lama memotong separuh alfabet.
     */
    protected const DICTIONARY_MAX_TOKENS = 6000;

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
        $dikenal = array_flip($dictionary);
        $changed = false;
        $corrected = [];

        foreach ($terms as $term) {
            // Kata gabungan dipecah lebih dulu, mis. "basisdata".
            $bagian = $this->splitCompoundTerm($term, $dikenal);

            if ($bagian !== [$term]) {
                $changed = true;
            }

            foreach ($bagian as $kata) {
                $fixed = $this->correctTerm($kata, $dictionary);
                $corrected[] = $fixed;

                if ($fixed !== $kata) {
                    $changed = true;
                }
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
            foreach (Author::query()->pluck('name') as $authorName) {
                $this->collectTokens((string) $authorName, $seen);
            }
            foreach (Category::query()->pluck('name') as $categoryName) {
                $this->collectTokens((string) $categoryName, $seen);
            }
            foreach (Skripsi::query()->whereNotNull('keywords')->pluck('keywords') as $keywords) {
                $this->collectTokens((string) $keywords, $seen);
            }
            foreach (Skripsi::query()->whereNotNull('author_name')->pluck('author_name') as $authorName) {
                $this->collectTokens((string) $authorName, $seen);
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

            // Bila perlu dipotong, utamakan kata yang paling sering muncul
            // agar kata umum tidak terbuang oleh urutan alfabetis.
            arsort($seen);

            $dictionary = array_slice(
                array_keys($seen),
                0,
                self::DICTIONARY_MAX_TOKENS,
            );

            sort($dictionary);

            return $dictionary;
        });
    }

    /**
     * @param  array<string, int>  $seen
     */
    protected function collectTokens(string $text, array &$seen): void
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);

        if ($words === false) {
            return;
        }

        foreach ($words as $word) {
            if (mb_strlen($word) >= 3) {
                $seen[$word] = ($seen[$word] ?? 0) + 1;
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

    /**
     * Pecah istilah gabungan menjadi kata-kata yang dikenali kamus.
     *
     * "basisdata" menjadi ["basis", "data"] bila kedua bagian ada di kamus.
     * Istilah yang sudah dikenali atau tidak dapat dipecah dikembalikan apa
     * adanya, sehingga pencarian yang sudah baik tidak terpengaruh.
     *
     * @param  array<string, true>  $dikenal
     * @return list<string>
     */
    public function splitCompoundTerm(string $term, array $dikenal): array
    {
        $panjang = mb_strlen($term);

        if ($panjang < 6 || isset($dikenal[$term])) {
            return [$term];
        }

        // Coba setiap titik potong; kedua bagian harus kata yang dikenali.
        for ($i = 3; $i <= $panjang - 3; $i++) {
            $kiri = mb_substr($term, 0, $i);
            $kanan = mb_substr($term, $i);

            if (isset($dikenal[$kiri], $dikenal[$kanan])) {
                return [$kiri, $kanan];
            }
        }

        return [$term];
    }
}
