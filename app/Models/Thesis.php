<?php

namespace App\Models;

use Database\Factories\ThesisFactory;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Thesis extends Model
{
    /** @use HasFactory<ThesisFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'author_name',
        'student_id',
        'year',
        'abstract',
        'keywords',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'view_count' => 'integer',
        ];
    }

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
            ->values()
            ->all();

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
        // Consistent with Book::scopeSearch and Post::scopeSearch.
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

    protected function supportsFullText(Connection $connection): bool
    {
        return in_array($connection->getDriverName(), ['mysql', 'mariadb'], true);
    }

    protected function toBooleanFullTextQuery(string $search): string
    {
        return Str::of($search)
            ->explode(' ')
            ->filter()
            ->map(fn (string $term): string => sprintf('%s*', $term))
            ->implode(' ');
    }
}
