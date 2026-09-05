<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InternshipReportSubmission extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_REVISION = 'revision';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'internship_report_id',
        'title',
        'company_name',
        'company_address',
        'academic_advisor',
        'field_advisor',
        'year',
        'abstract',
        'keywords',
        'report_file_path',
        'endorsement_file_path',
        'status',
        'revision_notes',
        'reviewed_by',
        'reviewed_at',
        'receipt_number',
        'receipt_token',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'reviewed_at' => 'datetime',
        ];
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

    public function internshipReport(): BelongsTo
    {
        return $this->belongsTo(InternshipReport::class);
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
