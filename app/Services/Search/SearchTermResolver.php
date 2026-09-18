<?php

declare(strict_types=1);

namespace App\Services\Search;

/**
 * Utilitas kata kunci pencarian dan pemilihan kata kunci terbaik.
 *
 * Koreksi ejaan hanya dipakai bila benar-benar menambah hasil, sehingga
 * pencarian yang sudah baik tidak pernah memburuk.
 */
class SearchTermResolver
{
    /** Ambang jumlah hasil yang dianggap terlalu sedikit. */
    public const MIN_ACCEPTABLE_RESULTS = 3;

    public function __construct(
        protected SearchTermCorrector $corrector,
    ) {}

    /**
     * Tentukan kata kunci yang benar-benar dipakai untuk pencarian.
     *
     * @param  callable(string): int  $count
     */
    public function resolve(
        string $search,
        callable $count,
        int $minAcceptable = self::MIN_ACCEPTABLE_RESULTS,
    ): string {
        if ($search === '' || mb_strlen($search) < 4) {
            return $search;
        }

        $asli = $count($search);

        if ($asli >= $minAcceptable) {
            return $search;
        }

        $koreksi = $this->corrector->correctQuery($search);

        if ($koreksi === null || $koreksi === $search) {
            return $search;
        }

        return $count($koreksi) > $asli ? $koreksi : $search;
    }

    /**
     * Kembalikan query terkoreksi (typo), atau null bila tidak ada perbaikan.
     */
    public function correct(string $search): ?string
    {
        return $this->corrector->correctQuery($search);
    }

    /**
     * Pecah query menjadi kata kunci yang aman dipakai pada klausa LIKE.
     *
     * @return list<string>
     */
    public function words(string $query): array
    {
        return collect(preg_split('/\s+/', mb_strtolower($query), -1, PREG_SPLIT_NO_EMPTY) ?: [])
            ->map(fn (string $word): string => $this->sanitizeLikeTerm($word))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Buang wildcard LIKE yang bisa mengubah semantik pencarian.
     */
    public function sanitizeLikeTerm(string $term): string
    {
        return str_replace(['\\', '%', '_'], '', $term);
    }
}
