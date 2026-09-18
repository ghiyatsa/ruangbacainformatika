<?php

declare(strict_types=1);

namespace App\Services\Search;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Skripsi;
use Illuminate\Database\Eloquent\Builder;

/**
 * Bangun saran pencarian bergaya autocomplete dari kategori, pengarang,
 * judul buku, dan kata kunci karya ilmiah.
 */
class SearchSuggestionBuilder
{
    /**
     * Kumpulkan saran teks berbasis frasa pencarian organik (Google/Gramedia style).
     *
     * @param  list<string>  $words
     * @return list<string>
     */
    public function collectMultiField(array $words, int $needed, bool $includeAcademic = true): array
    {
        if ($needed <= 0 || $words === []) {
            return [];
        }

        $results = [];
        $remaining = $needed;

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
    public function extractMeaningfulPhrase(string $title, array $words): ?string
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
     * Bersihkan teks saran agar konsisten seperti autocomplete.
     */
    public function format(string $text): string
    {
        return preg_replace('/[^\p{L}\p{N}\s\-\–]/u', '', mb_strtolower($text)) ?? '';
    }
}
