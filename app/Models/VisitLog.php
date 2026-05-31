<?php

namespace App\Models;

use App\Support\AppTimezone;
use Carbon\CarbonInterface;
use Database\Factories\VisitLogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class VisitLog extends Model
{
    /** @use HasFactory<VisitLogFactory> */
    use HasFactory;

    public const ADMIN_TIMEZONE = 'Asia/Jakarta';

    public const VISITOR_TYPE_MAHASISWA = 'mahasiswa';

    public const VISITOR_TYPE_DOSEN = 'dosen';

    public const VISITOR_TYPE_STAFF = 'staff';

    public const VISITOR_TYPE_UMUM = 'umum';

    protected $fillable = [
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

    public static function adminTimezone(): string
    {
        return AppTimezone::displayTimezone();
    }

    public function visitedAtForAdmin(): ?CarbonInterface
    {
        return AppTimezone::toDisplay($this->visited_at);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function adminDayRange(\DateTimeInterface|string|null $date = null): array
    {
        return AppTimezone::dayRange($date);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function adminWeekRange(?\DateTimeInterface $date = null): array
    {
        $week = $date instanceof \DateTimeInterface
            ? Carbon::instance($date)->setTimezone(self::adminTimezone())
            : AppTimezone::now();

        return [
            $week->copy()->startOfWeek()->utc(),
            $week->copy()->endOfWeek()->utc(),
        ];
    }

    public function scopeVisitedBetween(Builder $query, ?string $from, ?string $until): Builder
    {
        return $query
            ->when(filled($from), function (Builder $query) use ($from): Builder {
                [$startOfDay] = self::adminDayRange($from);

                return $query->where('visited_at', '>=', $startOfDay);
            })
            ->when(filled($until), function (Builder $query) use ($until): Builder {
                [, $endOfDay] = self::adminDayRange($until);

                return $query->where('visited_at', '<=', $endOfDay);
            });
    }

    /**
     * @return array{today:int,this_week:int,most_common_purpose:string,most_common_type:string}
     */
    public static function reportingSummary(): array
    {
        [$todayStart, $todayEnd] = self::adminDayRange();
        [$weekStart, $weekEnd] = self::adminWeekRange();

        $todayCount = static::query()
            ->whereBetween('visited_at', [$todayStart, $todayEnd])
            ->count();

        $thisWeekCount = static::query()
            ->whereBetween('visited_at', [$weekStart, $weekEnd])
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
