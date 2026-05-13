<?php

namespace App\Models;

use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory;

    public const STATUS_NEW = 'new';

    public const STATUS_READ = 'read';

    public const STATUS_REPLIED = 'replied';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'admin_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $contactMessage): void {
            if ($contactMessage->status === self::STATUS_NEW) {
                $contactMessage->reviewed_at = null;

                return;
            }

            $contactMessage->reviewed_at ??= now();
        });
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_NEW => 'Baru',
            self::STATUS_READ => 'Dibaca',
            self::STATUS_REPLIED => 'Sudah dibalas',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'warning',
            self::STATUS_READ => 'info',
            self::STATUS_REPLIED => 'success',
            default => 'gray',
        };
    }

    protected function preview(): Attribute
    {
        return Attribute::get(fn (): string => str($this->message)->squish()->limit(80)->value());
    }
}
