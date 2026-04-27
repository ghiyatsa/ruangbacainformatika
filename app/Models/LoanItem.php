<?php

namespace App\Models;

use Database\Factories\LoanItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanItem extends Model
{
    /** @use HasFactory<LoanItemFactory> */
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'book_item_id',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'returned_at' => 'datetime',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function bookItem(): BelongsTo
    {
        return $this->belongsTo(BookItem::class);
    }

    public function isReturned(): bool
    {
        return $this->returned_at !== null;
    }
}
