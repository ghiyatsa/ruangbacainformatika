<?php

namespace App\Models;

use Database\Factories\VisitLogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class VisitLog extends Model
{
    /** @use HasFactory<VisitLogFactory> */
    use HasFactory;

    public const VISITOR_TYPE_MAHASISWA = 'mahasiswa';

    public const VISITOR_TYPE_DOSEN = 'dosen';

    public const VISITOR_TYPE_STAFF = 'staff';

    public const VISITOR_TYPE_UMUM = 'umum';

    protected $fillable = [
        'kiosk_device_id',
        'name',
        'visitor_type',
        'identity_number',
        'institution',
        'phone',
        'purpose',
        'notes',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }

    public static function visitorTypeOptions(): array
    {
        return [
            self::VISITOR_TYPE_MAHASISWA => 'Mahasiswa',
            self::VISITOR_TYPE_DOSEN => 'Dosen',
            self::VISITOR_TYPE_STAFF => 'Staff',
            self::VISITOR_TYPE_UMUM => 'Umum',
        ];
    }

    public static function purposeOptions(): array
    {
        return [
            'read' => 'Baca di tempat',
            'borrow_return' => 'Pinjam / kembalikan buku',
            'reference' => 'Mencari referensi',
            'administration' => 'Administrasi',
            'other' => 'Lainnya',
        ];
    }

    public function kioskDevice(): BelongsTo
    {
        return $this->belongsTo(KioskDevice::class);
    }

    public function scopeVisitedBetween(Builder $query, ?string $from, ?string $until): Builder
    {
        return $query
            ->when(
                filled($from),
                fn (Builder $query): Builder => $query->where('visited_at', '>=', Carbon::parse($from)->startOfDay()),
            )
            ->when(
                filled($until),
                fn (Builder $query): Builder => $query->where('visited_at', '<=', Carbon::parse($until)->endOfDay()),
            );
    }

    /**
     * @return array{today:int,this_week:int,most_common_purpose:string,most_common_type:string}
     */
    public static function reportingSummary(): array
    {
        $todayCount = static::query()
            ->whereDate('visited_at', today())
            ->count();

        $thisWeekCount = static::query()
            ->whereBetween('visited_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $mostCommonPurpose = static::query()
            ->selectRaw('purpose, COUNT(*) as aggregate')
            ->groupBy('purpose')
            ->orderByDesc('aggregate')
            ->value('purpose');

        $mostCommonType = static::query()
            ->selectRaw('visitor_type, COUNT(*) as aggregate')
            ->groupBy('visitor_type')
            ->orderByDesc('aggregate')
            ->value('visitor_type');

        return [
            'today' => $todayCount,
            'this_week' => $thisWeekCount,
            'most_common_purpose' => static::purposeOptions()[$mostCommonPurpose] ?? 'Belum ada data',
            'most_common_type' => static::visitorTypeOptions()[$mostCommonType] ?? 'Belum ada data',
        ];
    }
}
