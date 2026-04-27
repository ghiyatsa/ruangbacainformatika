<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'bio',
    ];

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
