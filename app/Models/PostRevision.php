<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PostRevisionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Perubahan artikel yang menunggu tinjauan.
 *
 * Artikel yang sudah terbit tidak lagi "ditarik" saat penulis menyuntingnya.
 * Suntingan disimpan di sini dan versi lama tetap tampil sampai perubahan
 * disetujui oleh peninjau.
 */
class PostRevision extends Model
{
    /** @use HasFactory<PostRevisionFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'post_id',
        'user_id',
        'status',
        'title',
        'slug',
        'summary',
        'content',
        'cover_image',
        'allow_comments',
        'categories',
        'tags',
        'reviewed_by_user_id',
        'reviewed_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'allow_comments' => 'boolean',
            'categories' => 'array',
            'tags' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeWorking(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_REJECTED,
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draf Perubahan',
            self::STATUS_PENDING => 'Menunggu Tinjauan',
            self::STATUS_APPROVED => 'Diterbitkan',
            self::STATUS_REJECTED => 'Perlu Perbaikan',
            default => $this->status,
        };
    }
}
