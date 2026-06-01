<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Author extends Model
{
    use GeneratesSlug;
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'bio',
    ];

    protected static function slugSourceAttribute(): string
    {
        return 'name';
    }

    protected static function slugFallbackValue(): string
    {
        return 'penulis';
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class);
    }

    public function deletionBlockedReason(): ?string
    {
        $booksCount = $this->books()->count();

        if ($booksCount > 0) {
            return "Data penulis ini tidak dapat dihapus karena masih terhubung dengan {$booksCount} buku.";
        }

        return null;
    }
}
