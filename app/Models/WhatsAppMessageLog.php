<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'category',
    'notification_type',
    'phone_number_hash',
    'phone_number_masked',
    'status',
    'attempts',
    'provider_status',
    'provider_message_id',
    'message_preview',
    'response_payload',
    'error_message',
    'sent_at',
    'failed_at',
    'skipped_at',
])]
class WhatsAppMessageLog extends Model
{
    public const StatusPending = 'pending';

    public const StatusSent = 'sent';

    public const StatusFailed = 'failed';

    public const StatusSkipped = 'skipped';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'response_payload' => 'array',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
            'skipped_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    public function markSent(?array $payload = null): void
    {
        $this->forceFill([
            'status' => self::StatusSent,
            'attempts' => $this->attempts + 1,
            'provider_status' => $this->providerStatus($payload),
            'provider_message_id' => $this->providerMessageId($payload),
            'response_payload' => $payload,
            'sent_at' => now(),
            'failed_at' => null,
        ])->save();
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    public function markFailed(string $errorMessage, ?array $payload = null): void
    {
        $this->forceFill([
            'status' => self::StatusFailed,
            'attempts' => $this->attempts + 1,
            'provider_status' => $this->providerStatus($payload),
            'provider_message_id' => $this->providerMessageId($payload),
            'response_payload' => $payload,
            'error_message' => Str::limit($errorMessage, 1000, ''),
            'failed_at' => now(),
        ])->save();
    }

    public function markSkipped(string $reason): void
    {
        $this->forceFill([
            'status' => self::StatusSkipped,
            'error_message' => Str::limit($reason, 1000, ''),
            'skipped_at' => now(),
        ])->save();
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    protected function providerStatus(?array $payload): ?string
    {
        $status = $payload['status'] ?? $payload['Status'] ?? null;

        if (is_bool($status)) {
            return $status ? 'accepted' : 'rejected';
        }

        return is_scalar($status) ? (string) $status : null;
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    protected function providerMessageId(?array $payload): ?string
    {
        $messageId = $payload['id'] ?? $payload['message_id'] ?? $payload['messageId'] ?? null;

        return is_scalar($messageId) ? (string) $messageId : null;
    }
}
