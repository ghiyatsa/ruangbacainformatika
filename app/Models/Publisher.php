<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publisher extends Model
{
    use GeneratesSlug;
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'city',
        'description',
    ];

    protected static function slugSourceAttribute(): string
    {
        return 'name';
    }

    protected static function slugFallbackValue(): string
    {
        return 'penerbit';
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function deletionBlockedReason(): ?string
    {
        $booksCount = $this->books()->count();

        if ($booksCount > 0) {
            return "Data penerbit ini tidak dapat dihapus karena masih digunakan oleh {$booksCount} buku.";
        }

        return null;
    }
}
