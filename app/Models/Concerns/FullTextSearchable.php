<?php

namespace App\Models\Concerns;

use Illuminate\Database\Connection;
use Illuminate\Support\Str;

trait FullTextSearchable
{
    protected function supportsFullText(Connection $connection): bool
    {
        return in_array($connection->getDriverName(), ['mysql', 'mariadb'], true);
    }

    protected function toBooleanFullTextQuery(string $search): string
    {
        return Str::of($search)
            ->explode(' ')
            ->filter()
            ->map(fn (string $term): string => sprintf('%s*', $this->sanitizeFullTextTerm($term)))
            ->implode(' ');
    }

    protected function sanitizeFullTextTerm(string $term): string
    {
        $clean = preg_replace('/[^\p{L}\p{N}]+/u', '', $term) ?? '';

        return mb_strtolower($clean);
    }

    protected function sanitizeLikeTerm(string $term): string
    {
        return str_replace(['\\', '%', '_'], '', $term);
    }

    /**
     * Kata umum yang tidak pernah menjadi kata kunci pencarian buku.
     * Membiarkannya membuat kueri seperti "buku yang salah" cocok dengan
     * hampir seluruh katalog.
     *
     * @var list<string>
     */
    protected const SEARCH_STOPWORDS = [
        'dan', 'atau', 'yang', 'untuk', 'dengan', 'di', 'ke', 'dari', 'pada',
        'adalah', 'ini', 'itu', 'sebagai', 'oleh', 'the', 'of', 'and',
    ];

    /**
     * Pecah kata kunci menjadi istilah pencarian yang siap dipakai.
     *
     * Tanda hubung dan tanda baca diubah menjadi spasi supaya "dasar-dasar",
     * "dasar dasar", dan "dasardasar" menghasilkan istilah yang sama. Kata
     * umum dibuang; bila seluruh kata kunci hanyalah kata umum, istilah asli
     * tetap dipakai agar pencarian tidak menjadi kosong.
     *
     * @return list<string>
     */
    protected function searchTerms(string $search): array
    {
        $normalized = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $search) ?? '';

        $terms = Str::of($normalized)
            ->lower()
            ->explode(' ')
            ->map(fn (string $term): string => trim($term))
            ->filter()
            ->values();

        // Seluruh istilah asli dipakai agar kueri makin spesifik; kata umum
        // tidak dibuang karena sering menjadi bagian judul karya, misalnya
        // "Digital Marketing Untuk Kreator Konten". Kata umum hanya dibuang
        // bila kueri tidak berisi istilah bermakna selain kata umum, sehingga
        // pencarian seperti "dan" tetap berjalan apa adanya.
        $adaIstilahBermakna = $terms
            ->reject(fn (string $term): bool => in_array($term, self::SEARCH_STOPWORDS, true))
            ->isNotEmpty();

        $filtered = $adaIstilahBermakna
            ? $terms
            : $terms->reject(fn (string $term): bool => in_array($term, self::SEARCH_STOPWORDS, true))->values();

        $hasil = $filtered->isEmpty() ? $terms : $filtered;

        // Kata ulang tanpa pemisah, mis. "dasardasar" -> ["dasar", "dasar"].
        // Hal ini lazim pada judul Indonesia seperti "Dasar-Dasar".
        $hasil = $hasil->flatMap(function (string $term): array {
            $panjang = mb_strlen($term);

            if ($panjang >= 4 && $panjang % 2 === 0) {
                $separuh = mb_substr($term, 0, intdiv($panjang, 2));

                if ($separuh === mb_substr($term, intdiv($panjang, 2))) {
                    return [$separuh, $separuh];
                }
            }

            return [$term];
        })->values();

        return $hasil->all();
    }

    /**
     * Susun kueri boolean MySQL yang mewajibkan setiap istilah ada,
     * sehingga hasil harus memuat semua kata, bukan salah satu saja.
     *
     * @param  list<string>  $terms
     */
    protected function requiredBooleanFullTextQuery(array $terms): string
    {
        return collect($terms)
            ->map(fn (string $term): string => $this->sanitizeFullTextTerm($term))
            ->filter()
            ->map(fn (string $term): string => '+'.$term.'*')
            ->implode(' ');
    }
}
