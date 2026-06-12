<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use App\Support\Isbn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
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
            'is_featured' => 'boolean',
            'is_borrowable' => 'boolean',
            'is_published' => 'boolean',
            'cover_image_editor_state' => 'array',
            'published_year' => 'integer',
            'pages' => 'string',
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

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        if ($search === '') {
            return $query;
        }

        $terms = collect(explode(' ', $search))->filter()->values();

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
