<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait SearchableAcademic
{
    use FullTextSearchable;

    public function scopeSearch(Builder $query, string $search): Builder
    {
        if ($search === '') {
            return $query;
        }

        return $this->applyAcademicSearch($query, $search);
    }

    protected function applyAcademicSearch(Builder $query, string $search): Builder
    {
        $connection = $query->getConnection();
        $terms = collect(preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY))
            ->filter()
            ->map(fn (string $term): string => $this->sanitizeLikeTerm($term))
            ->filter()
            ->values()
            ->all();

        if ($terms === []) {
            return $query->whereRaw('1 = 0');
        }

        if ($this->supportsFullText($connection)) {
            // On fulltext-capable DBs (MySQL/MariaDB): use MATCH as the primary
            // filter. Per-term LIKE groups serve as OR fallbacks for edge cases
            // such as terms below the engine's minimum word length.
            return $query->where(function (Builder $searchQuery) use ($search, $terms): void {
                $searchQuery->whereRaw(
                    'MATCH(title, author_name, abstract, keywords) AGAINST (? IN BOOLEAN MODE)',
                    [$this->toBooleanFullTextQuery($search)]
                );

                foreach ($terms as $term) {
                    $searchQuery->orWhere(function (Builder $inner) use ($term): void {
                        $inner->where('title', 'like', "%{$term}%")
                            ->orWhere('author_name', 'like', "%{$term}%")
                            ->orWhere('abstract', 'like', "%{$term}%")
                            ->orWhere('keywords', 'like', "%{$term}%")
                            ->orWhere('student_id', 'like', "%{$term}%");
                    });
                }
            });
        }

        // Non-fulltext DB (e.g. SQLite in tests): AND-per-term, OR-per-field.
        return $query->where(function (Builder $outer) use ($terms): void {
            foreach ($terms as $term) {
                $outer->where(function (Builder $inner) use ($term): void {
                    $inner->where('title', 'like', "%{$term}%")
                        ->orWhere('author_name', 'like', "%{$term}%")
                        ->orWhere('abstract', 'like', "%{$term}%")
                        ->orWhere('keywords', 'like', "%{$term}%")
                        ->orWhere('student_id', 'like', "%{$term}%");
                });
            }
        });
    }
}
