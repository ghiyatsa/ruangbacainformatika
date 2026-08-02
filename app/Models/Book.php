<?php

namespace App\Models;

use App\Models\Concerns\FullTextSearchable;
use App\Models\Concerns\GeneratesSlug;
use App\Support\Casts\IssnCast;
use App\Support\Casts\SquishCast;
use App\Support\Casts\TitleCaseCast;
use App\Support\Isbn;
use App\Support\MetadataCompleteness;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
    use FullTextSearchable;
    use GeneratesSlug;
    use HasFactory;

    protected static function booted(): void
    {
        static::deleted(function (Book $book) {
            if ($book->cover_image && Storage::disk('public')->exists($book->cover_image)) {
                Storage::disk('public')->delete($book->cover_image);
            }
        });

        static::updating(function (Book $book) {
            if ($book->isDirty('cover_image')) {
                $oldImage = $book->getOriginal('cover_image');
                if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                    Storage::disk('public')->delete($oldImage);
                }
            }
        });
    }

    protected $fillable = [
        'title',
        'subtitle',
        'slug',
        'isbn',
        'issn',
        'ddc_code',
        'description',
        'cover_image',
        'cover_image_editor_state',
        'edition',
        'published_year',
        'pages',
        'language',
        'is_featured',
        'is_borrowable',
        'is_published',
        'view_count',
        'publisher_id',
    ];

    protected function casts(): array
    {
        return [
            'title' => TitleCaseCast::class,
            'subtitle' => TitleCaseCast::class,
            'description' => SquishCast::class,
            'edition' => SquishCast::class,
            'pages' => SquishCast::class,
            'ddc_code' => SquishCast::class,
            'issn' => IssnCast::class,
            'is_featured' => 'boolean',
            'is_borrowable' => 'boolean',
            'is_published' => 'boolean',
            'cover_image_editor_state' => 'array',
            'published_year' => 'integer',
            'view_count' => 'integer',
        ];
    }

    protected function isbn(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => Isbn::normalize($value),
        );
    }

    protected static function slugSourceAttribute(): string
    {
        return 'title';
    }

    protected static function slugFallbackValue(): string
    {
        return 'buku';
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookItem::class);
    }

    public function loanItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            LoanItem::class,
            BookItem::class,
            'book_id',
            'book_item_id',
            'id',
            'id',
        );
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function getMetadataCompletenessAttribute(): array
    {
        return MetadataCompleteness::evaluate($this);
    }

    public function getMetadataScoreAttribute(): int
    {
        return $this->metadata_completeness['score'];
    }

    public function getMetadataLevelAttribute(): string
    {
        return $this->metadata_completeness['level'];
    }

    public function getMetadataMissingAttribute(): array
    {
        return $this->metadata_completeness['missing'];
    }

    public function scopeMetadataLevel(Builder $query, string $level): Builder
    {
        $scoreSql = 'ROUND(('.MetadataCompleteness::filledSql().') / '.MetadataCompleteness::total().' * 100)';

        return match ($level) {
            MetadataCompleteness::LEVEL_LENGKAP => $query->whereRaw("{$scoreSql} >= 100"),
            MetadataCompleteness::LEVEL_SEBAGIAN => $query->whereRaw("{$scoreSql} >= 50 AND {$scoreSql} < 100"),
            MetadataCompleteness::LEVEL_KURANG => $query->whereRaw("{$scoreSql} < 50"),
            default => $query,
        };
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        if ($search === '') {
            return $query;
        }

        $terms = collect(explode(' ', $search))
            ->filter()
            ->map(fn (string $term): string => $this->sanitizeLikeTerm($term))
            ->filter()
            ->values();

        if ($terms->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        if ($this->supportsFullText($query->getConnection())) {
            return $query->where(function (Builder $q) use ($search, $terms) {
                $q->whereRaw(
                    'MATCH(title, subtitle, description, isbn, issn, ddc_code) AGAINST (? IN BOOLEAN MODE)',
                    [$this->toBooleanFullTextQuery($search)]
                );

                foreach ($terms as $term) {
                    $q->orWhere(function (Builder $inner) use ($term) {
                        $inner->where('title', 'like', "%{$term}%")
                            ->orWhere('subtitle', 'like', "%{$term}%")
                            ->orWhere('isbn', 'like', "%{$term}%")
                            ->orWhere('issn', 'like', "%{$term}%")
                            ->orWhere('ddc_code', 'like', "%{$term}%")
                            ->orWhere('description', 'like', "%{$term}%")
                            ->orWhereHas('publisher', fn (Builder $p) => $p->where('name', 'like', "%{$term}%"))
                            ->orWhereHas('authors', fn (Builder $a) => $a->where('name', 'like', "%{$term}%"))
                            ->orWhereHas('categories', fn (Builder $c) => $c->where('name', 'like', "%{$term}%"));
                    });
                }
            });
        }

        return $query->where(function (Builder $q) use ($terms) {
            foreach ($terms as $term) {
                $q->where(function (Builder $inner) use ($term) {
                    $inner->where('title', 'like', "%{$term}%")
                        ->orWhere('subtitle', 'like', "%{$term}%")
                        ->orWhere('isbn', 'like', "%{$term}%")
                        ->orWhere('issn', 'like', "%{$term}%")
                        ->orWhere('ddc_code', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhereHas('publisher', fn (Builder $p) => $p->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('authors', fn (Builder $a) => $a->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('categories', fn (Builder $c) => $c->where('name', 'like', "%{$term}%"));
                });
            }
        });
    }

    public function canBeBorrowed(): bool
    {
        return $this->is_borrowable;
    }

    public function scopeForCategory(Builder $query, string $categorySlug): Builder
    {
        if ($categorySlug === '') {
            return $query;
        }

        return $query->whereHas(
            'categories',
            fn (Builder $q): Builder => $q->where('slug', $categorySlug),
        );
    }

    public function scopeForAuthor(Builder $query, string $authorSlug): Builder
    {
        if ($authorSlug === '') {
            return $query;
        }

        return $query->whereHas(
            'authors',
            fn (Builder $q): Builder => $q->where('slug', $authorSlug),
        );
    }

    public function scopeForPublisher(Builder $query, string $publisherSlug): Builder
    {
        if ($publisherSlug === '') {
            return $query;
        }

        return $query->whereHas(
            'publisher',
            fn (Builder $q): Builder => $q->where('slug', $publisherSlug),
        );
    }

    public function scopeForYear(Builder $query, ?int $year): Builder
    {
        if (! $year) {
            return $query;
        }

        return $query->where('published_year', $year);
    }

    public function scopeOnlyAvailable(Builder $query, bool $available = true): Builder
    {
        if (! $available) {
            return $query;
        }

        return $query->whereHas('items', fn (Builder $q) => $q->available());
    }

    public function canBeDeleted(): bool
    {
        return $this->deletionBlockedReason() === null;
    }

    public function deletionBlockedReason(): ?string
    {
        if ($this->items()->whereHas('loanItems')->exists()) {
            return 'Data buku ini tidak dapat dihapus karena satu atau lebih eksemplarnya telah memiliki riwayat transaksi peminjaman.';
        }

        return null;
    }
}
