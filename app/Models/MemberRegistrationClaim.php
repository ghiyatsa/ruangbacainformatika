<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name',
    'email',
    'whatsapp',
    'address',
    'token_hash',
    'status',
    'expires_at',
    'claimed_at',
    'last_error_message',
    'last_error_at',
    'user_id',
])]
class MemberRegistrationClaim extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_LINKED = 'linked';

    public const STATUS_CLAIMED = 'claimed';

    public const STATUS_EXPIRED = 'expired';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'claimed_at' => 'datetime',
            'last_error_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function markAsExpired(): void
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return;
        }

        $this->forceFill([
            'status' => self::STATUS_EXPIRED,
        ])->save();
    }

    public function recordAttemptError(string $message): void
    {
        $this->forceFill([
            'last_error_message' => $message,
            'last_error_at' => now(),
        ])->save();
    }

    public function clearAttemptError(): void
    {
        if ($this->last_error_message === null && $this->last_error_at === null) {
            return;
        }

        $this->forceFill([
            'last_error_message' => null,
            'last_error_at' => null,
        ])->save();
    }
}
