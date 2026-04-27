<?php

namespace App\Models;

use Database\Factories\KioskDeviceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class KioskDevice extends Model
{
    /** @use HasFactory<KioskDeviceFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'name',
        'kiosk_identifier',
        'registration_code',
        'device_token_hash',
        'status',
        'ip_address',
        'user_agent',
        'last_seen_at',
        'approved_at',
        'approved_by',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
            'approved_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isRevoked(): bool
    {
        return $this->status === self::STATUS_REVOKED;
    }

    public function connectivityStatus(): string
    {
        if (blank($this->last_seen_at)) {
            return 'offline';
        }

        $lastSeenAt = $this->last_seen_at instanceof Carbon
            ? $this->last_seen_at
            : Carbon::parse($this->last_seen_at);

        $minutesSinceLastSeen = $lastSeenAt->diffInMinutes(now());

        return match (true) {
            $minutesSinceLastSeen <= 5 => 'online',
            $minutesSinceLastSeen <= 60 => 'stale',
            default => 'offline',
        };
    }

    public function isOnline(): bool
    {
        return $this->connectivityStatus() === 'online';
    }

    public function issueAccessToken(): string
    {
        $plainTextToken = Str::random(64);

        $this->forceFill([
            'device_token_hash' => Hash::make($plainTextToken),
        ])->save();

        return $plainTextToken;
    }

    public function hasValidToken(?string $token): bool
    {
        return filled($token)
            && filled($this->device_token_hash)
            && Hash::check($token, $this->device_token_hash);
    }

    public function approve(int $approvedBy): void
    {
        $this->forceFill([
            'status' => self::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $approvedBy,
            'revoked_at' => null,
        ])->save();
    }

    public function reject(): void
    {
        $this->forceFill([
            'status' => self::STATUS_REJECTED,
            'approved_at' => null,
            'approved_by' => null,
            'revoked_at' => null,
        ])->save();
    }

    public function revoke(): void
    {
        $this->forceFill([
            'status' => self::STATUS_REVOKED,
            'revoked_at' => now(),
        ])->save();
    }

    public function markAsPending(): void
    {
        $this->forceFill([
            'status' => self::STATUS_PENDING,
            'approved_at' => null,
            'approved_by' => null,
            'revoked_at' => null,
        ])->save();
    }
}
