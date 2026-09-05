<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class DocumentSubmission extends Model
{
    use HasFactory;

    public const TYPE_INTERNSHIP_REPORT = 'internship_report';

    public const TYPE_SKRIPSI = 'skripsi';

    public const TYPE_BOOK_DONATION = 'book_donation';

    public const STATUS_PENDING = 'pending';

    public const STATUS_REVISION = 'revision';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'type',
        'submittable_type',
        'submittable_id',
        'status',
        'title',
        'subtitle',
        'slug',
        'description',
        'year',
        'abstract',
        'keywords',
        'academic_advisor',
        'company_name',
        'company_address',
        'field_advisor',
        'author_names',
        'publisher_name',
        'publisher_id',
        'author_ids',
        'category_ids',
        'isbn',
        'issn',
        'ddc_code',
        'language',
        'edition',
        'pages',
        'copies_count',
        'book_condition',
        'cover_image',
        'document_file_path',
        'endorsement_file_path',
        'revision_notes',
        'reviewed_by',
        'reviewed_at',
        'receipt_number',
        'receipt_token',
        'submission_batch_token',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'copies_count' => 'integer',
            'author_ids' => 'array',
            'category_ids' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $submission): void {
            if (empty($submission->receipt_token)) {
                $submission->receipt_token = Str::random(40);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function submittable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRevision(): bool
    {
        return $this->status === self::STATUS_REVISION;
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_INTERNSHIP_REPORT => 'Laporan KP',
            self::TYPE_SKRIPSI => 'Skripsi',
            self::TYPE_BOOK_DONATION => 'Sumbangan Buku',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    public function typeBadgeColor(): string
    {
        return match ($this->type) {
            self::TYPE_INTERNSHIP_REPORT => 'warning',
            self::TYPE_SKRIPSI => 'info',
            self::TYPE_BOOK_DONATION => 'purple',
            default => 'gray',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu Verifikasi',
            self::STATUS_REVISION => 'Perlu Revisi',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            default => 'Pending',
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'amber',
            self::STATUS_REVISION => 'rose',
            self::STATUS_APPROVED => 'emerald',
            self::STATUS_REJECTED => 'gray',
            default => 'gray',
        };
    }
}
