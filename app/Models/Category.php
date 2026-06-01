<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use GeneratesSlug;
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    protected static function slugSourceAttribute(): string
    {
        return 'name';
    }

    protected static function slugFallbackValue(): string
    {
        return 'kategori';
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class);
    }

    public function deletionBlockedReason(): ?string
    {
        $booksCount = $this->books()->count();

        if ($booksCount > 0) {
            return "Data kategori ini tidak dapat dihapus karena masih digunakan oleh {$booksCount} buku.";
        }

        return null;
    }
}
