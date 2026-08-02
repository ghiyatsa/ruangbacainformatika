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
}
