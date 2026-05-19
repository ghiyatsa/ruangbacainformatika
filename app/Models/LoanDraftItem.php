<?php

namespace App\Models;

use Database\Factories\LoanDraftItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanDraftItem extends Model
{
    /** @use HasFactory<LoanDraftItemFactory> */
    use HasFactory;

    protected $fillable = [
        'loan_draft_id',
        'book_id',
    ];

    public function loanDraft(): BelongsTo
    {
        return $this->belongsTo(LoanDraft::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
