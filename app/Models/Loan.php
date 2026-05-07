<?php

namespace App\Models;

use Database\Factories\LoanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Loan extends Model
{
    /** @use HasFactory<LoanFactory> */
    use HasFactory;

    public const STATUS_BORROWED = 'borrowed';

    public const STATUS_RETURNED = 'returned';

    protected $fillable = [
        'user_id',
        'status',
        'borrowed_at',
        'due_at',
        'returned_at',
        'reminder_sent_at',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_BORROWED => 'Sedang Dipinjam',
            self::STATUS_RETURNED => 'Sudah Dikembalikan',
        ];
    }

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function isReturned(): bool
    {
        return $this->status === self::STATUS_RETURNED;
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_BORROWED
            && $this->due_at instanceof Carbon
            && $this->due_at->isPast();
    }

    public function deletionBlockedReason(): ?string
    {
        if ($this->status === self::STATUS_BORROWED) {
            return 'Transaksi peminjaman ini tidak dapat dihapus karena masih berstatus aktif.';
        }

        if ($this->items()->whereNull('returned_at', 'and', false)->exists()) {
            return 'Transaksi peminjaman ini tidak dapat dihapus karena masih terdapat item yang belum dikembalikan.';
        }

        return null;
    }
}
