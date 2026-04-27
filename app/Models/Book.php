<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
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
        'slug',
        'isbn',
        'ddc_code',
        'description',
        'cover_image',
        'edition',
        'published_year',
        'pages',
        'language',
        'is_featured',
        'is_published',
        'view_count',
        'publisher_id',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'published_year' => 'integer',
        'pages' => 'integer',
        'view_count' => 'integer',
    ];

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

        return $query->where(fn (Builder $q) => $q
            ->where('title', 'like', "%{$search}%")
            ->orWhere('isbn', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->orWhereHas('publisher', fn (Builder $p): Builder => $p->where('name', 'like', "%{$search}%"))
            ->orWhereHas('authors', fn (Builder $a): Builder => $a->where('name', 'like', "%{$search}%"))
            ->orWhereHas('categories', fn (Builder $c): Builder => $c->where('name', 'like', "%{$search}%")));
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
