<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'internal_code',
        'shelf_location',
        'condition',
        'status',
        'acquired_date',
        'price',
    ];

    protected $casts = [
        'acquired_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function loanItems(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    public function canBeDeleted(): bool
    {
        return $this->deletionBlockedReason() === null;
    }

    public function deletionBlockedReason(): ?string
    {
        if ($this->loanItems()->exists()) {
            return 'Data eksemplar ini tidak dapat dihapus karena telah memiliki riwayat transaksi peminjaman.';
        }

        return null;
    }
}
