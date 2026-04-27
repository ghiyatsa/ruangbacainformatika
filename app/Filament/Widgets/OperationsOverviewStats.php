<?php

namespace App\Filament\Widgets;

use App\Models\KioskDevice;
use App\Models\Loan;
use App\Models\LoanItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class OperationsOverviewStats extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected ?string $pollingInterval = '60s';

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Operasional Hari Ini';

    protected ?string $description = 'Ringkasan cepat untuk memantau sirkulasi dan perangkat kiosk.';

    protected function getStats(): array
    {
        return [
            Stat::make('Peminjaman Aktif', Loan::query()->where('status', Loan::STATUS_BORROWED)->count())
                ->description('Transaksi yang masih memiliki item belum kembali.')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->chart($this->getActiveLoansTrend())
                ->color('warning'),
            Stat::make('Pengembalian Hari Ini', LoanItem::query()->whereDate('returned_at', today())->count())
                ->description('Jumlah item yang selesai dikembalikan hari ini.')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->chart($this->getReturnedItemsTrend())
                ->color('success'),
            Stat::make('Kiosk Menunggu Approval', KioskDevice::query()->where('status', KioskDevice::STATUS_PENDING)->count())
                ->description('Perangkat baru yang masih menunggu persetujuan.')
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('primary'),
            Stat::make('Kiosk Offline > 1 Jam', KioskDevice::query()
                ->where(fn ($query) => $query
                    ->whereNull('last_seen_at')
                    ->orWhere('last_seen_at', '<', now()->subHour()))
                ->count())
                ->description('Perangkat yang perlu dicek koneksi atau statusnya.')
                ->descriptionIcon('heroicon-m-signal-slash')
                ->color('danger'),
        ];
    }

    /**
     * @return array<int, int>
     */
    protected function getActiveLoansTrend(): array
    {
        $startDate = now()->subDays(6)->startOfDay();

        $counts = Loan::query()
            ->selectRaw('DATE(borrowed_at) as date, COUNT(*) as aggregate')
            ->where('status', Loan::STATUS_BORROWED)
            ->where('borrowed_at', '>=', $startDate)
            ->groupBy('date')
            ->pluck('aggregate', 'date');

        return collect(range(0, 6))
            ->map(function (int $offset) use ($counts, $startDate): int {
                $date = Carbon::parse($startDate)->addDays($offset)->toDateString();

                return (int) ($counts[$date] ?? 0);
            })
            ->all();
    }

    /**
     * @return array<int, int>
     */
    protected function getReturnedItemsTrend(): array
    {
        $startDate = now()->subDays(6)->startOfDay();

        $counts = LoanItem::query()
            ->selectRaw('DATE(returned_at) as date, COUNT(*) as aggregate')
            ->whereNotNull('returned_at')
            ->where('returned_at', '>=', $startDate)
            ->groupBy('date')
            ->pluck('aggregate', 'date');

        return collect(range(0, 6))
            ->map(function (int $offset) use ($counts, $startDate): int {
                $date = Carbon::parse($startDate)->addDays($offset)->toDateString();

                return (int) ($counts[$date] ?? 0);
            })
            ->all();
    }
}
