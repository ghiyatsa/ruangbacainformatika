<?php

namespace App\Models;

use Database\Factories\SimilaritySyncStatusFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class SimilaritySyncStatus extends Model
{
    /** @use HasFactory<SimilaritySyncStatusFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_SYNCING = 'syncing';

    public const STATUS_SYNCED = 'synced';

    public const STATUS_FAILED = 'failed';

    public const OPERATION_UPSERT = 'upsert';

    public const OPERATION_DELETE = 'delete';

    protected $fillable = [
        'syncable_id',
        'syncable_type',
        'status',
        'last_operation',
        'attempts',
        'last_attempt_at',
        'last_synced_at',
        'last_error',
    ];

    protected $casts = [
        'last_attempt_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_SYNCING => 'Diproses',
            self::STATUS_SYNCED => 'Sinkron',
            self::STATUS_FAILED => 'Gagal',
        ];
    }

    public function syncable(): MorphTo
    {
        return $this->morphTo();
    }

    public function skripsi(): BelongsTo
    {
        return $this->belongsTo(Skripsi::class, 'syncable_id', 'id');
    }

    public function scopeForExistingRecords(Builder $query): Builder
    {
        return $query->whereHas('syncable');
    }

    public function scopeForExistingSkripsi(Builder $query): Builder
    {
        return $query->whereHas('skripsi');
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_SYNCING => 'info',
            self::STATUS_SYNCED => 'success',
            self::STATUS_FAILED => 'danger',
            default => 'gray',
        };
    }

    public function operationLabel(): string
    {
        return match ($this->last_operation) {
            self::OPERATION_DELETE => 'Penghapusan',
            default => 'Sinkronisasi',
        };
    }

    public function summary(): string
    {
        return match ($this->status) {
            self::STATUS_SYNCED => $this->last_synced_at
                ? 'Berhasil '.$this->last_synced_at->format('d/m/Y H:i')
                : 'Berhasil disinkronkan.',
            self::STATUS_FAILED => filled($this->last_error)
                ? Str::limit($this->last_error, 90)
                : 'Sinkronisasi gagal.',
            self::STATUS_SYNCING => $this->last_attempt_at
                ? 'Diproses sejak '.$this->last_attempt_at->format('d/m/Y H:i')
                : 'Sedang diproses.',
            self::STATUS_PENDING => 'Menunggu proses queue.',
            default => 'Belum ada status sinkronisasi.',
        };
    }
}
