<?php

namespace App\Models;

use Database\Factories\ReturnDraftItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnDraftItem extends Model
{
    /** @use HasFactory<ReturnDraftItemFactory> */
    use HasFactory;

    protected $fillable = [
        'return_draft_id',
        'loan_item_id',
    ];

    public function returnDraft(): BelongsTo
    {
        return $this->belongsTo(ReturnDraft::class);
    }

    public function loanItem(): BelongsTo
    {
        return $this->belongsTo(LoanItem::class);
    }
}
