<?php

namespace App\Models;

use Database\Factories\CatalogReportFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CatalogReport extends Model
{
    /** @use HasFactory<CatalogReportFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_REVIEWED = 'reviewed';

    public const STATUS_RESOLVED = 'resolved';

    public const CATALOG_TYPE_BOOK = 'book';

    public const CATALOG_TYPE_SKRIPSI = 'skripsi';

    public const CATALOG_TYPE_THESIS = 'thesis';

    public const CATALOG_TYPE_INTERNSHIP_REPORT = 'internship_report';

    protected $fillable = [
        'user_id',
        'catalog_type',
        'catalog_title',
        'catalog_url',
        'reporter_name',
        'reporter_email',
        'message',
        'status',
        'admin_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_REVIEWED => 'Ditinjau',
            self::STATUS_RESOLVED => 'Selesai',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function catalogTypeOptions(): array
    {
        return [
            self::CATALOG_TYPE_BOOK => 'Buku',
            self::CATALOG_TYPE_SKRIPSI => 'Skripsi',
            self::CATALOG_TYPE_THESIS => 'Tesis',
            self::CATALOG_TYPE_INTERNSHIP_REPORT => 'Laporan KP',
        ];
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_REVIEWED => 'info',
            self::STATUS_RESOLVED => 'success',
            default => 'gray',
        };
    }

    public function catalogTypeLabel(): string
    {
        return self::catalogTypeOptions()[$this->catalog_type] ?? $this->catalog_type;
    }

    public function publicUrl(): ?string
    {
        return $this->catalog_url ?? match (true) {
            $this->reportable instanceof Book => route('books.show', $this->reportable, absolute: false),
            $this->reportable instanceof Skripsi => route('skripsi.show', ['skripsi' => $this->reportable->student_id], absolute: false),
            $this->reportable instanceof Thesis => route('thesis.show', ['thesis' => $this->reportable->student_id], absolute: false),
            $this->reportable instanceof InternshipReport => route('internship-reports.show', ['internshipReport' => $this->reportable->student_id], absolute: false),
            default => null,
        };
    }

    protected function reporterDisplayName(): Attribute
    {
        return Attribute::get(fn (): string => $this->reporter_name ?? $this->user?->name ?? 'Anonim');
    }
}
