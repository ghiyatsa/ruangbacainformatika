<?php

namespace App\Models;

use Database\Factories\SkripsiFactory;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Skripsi extends Model
{
    /** @use HasFactory<SkripsiFactory> */
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

        return $query->where(function (Builder $searchQuery) use ($connection, $search): void {
            if ($this->supportsFullText($connection)) {
                $searchQuery->whereRaw(
                    'MATCH(title, author_name, abstract, keywords) AGAINST (? IN BOOLEAN MODE)',
                    [$this->toBooleanFullTextQuery($search)]
                );
            }

            $searchQuery->orWhere('title', 'like', "%{$search}%")
                ->orWhere('author_name', 'like', "%{$search}%")
                ->orWhere('abstract', 'like', "%{$search}%")
                ->orWhere('keywords', 'like', "%{$search}%")
                ->orWhere('student_id', 'like', "%{$search}%");
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

    public function similaritySyncStatus(): HasOne
    {
        return $this->hasOne(SimilaritySyncStatus::class, 'source_skripsi_id', 'id');
    }

    public function similaritySyncStatusLabel(): string
    {
        return $this->similaritySyncStatus?->statusLabel() ?? 'Belum';
    }

    public function similaritySyncStatusColor(): string
    {
        return $this->similaritySyncStatus?->statusColor() ?? 'gray';
    }
}
