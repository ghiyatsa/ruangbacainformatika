<?php

namespace App\Filament\Widgets;

use App\Models\VisitLog;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VisitOverviewStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '60s';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $summary = VisitLog::reportingSummary();

        return [
            Stat::make('Kunjungan Hari Ini', $summary['today'])
                ->description('Jumlah pengunjung yang tercatat hari ini.')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
            Stat::make('Kunjungan Minggu Ini', $summary['this_week'])
                ->description('Akumulasi kunjungan dari awal minggu sampai sekarang.')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
            Stat::make('Tujuan Terbanyak', $summary['most_common_purpose'])
                ->description('Tujuan kunjungan yang paling sering dipilih.')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('warning'),
            Stat::make('Tipe Pengunjung Dominan', $summary['most_common_type'])
                ->description('Segment pengunjung yang paling sering datang.')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
