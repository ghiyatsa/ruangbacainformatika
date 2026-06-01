<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use App\Models\VisitLog;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class LoanActivityChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Aktivitas Mingguan';

    protected ?string $description = 'Tren peminjaman dan kunjungan 7 hari terakhir.';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(
            fn (int $daysAgo): \DateTimeInterface => now(VisitLog::adminTimezone())->subDays($daysAgo)->startOfDay(),
        );

        $loanData = $days->map(
            fn (\DateTimeInterface $day): int => Loan::query()
                ->whereDate('borrowed_at', '=', $day, 'and')
                ->count('*')
        );

        $visitorData = $days->map(function (\DateTimeInterface $day): int {
            [$startOfDay, $endOfDay] = VisitLog::adminDayRange($day);

            return VisitLog::query()
                ->whereBetween('visited_at', [$startOfDay, $endOfDay], 'and')
                ->count('*');
        });

        $labels = $days->map(fn (\DateTimeInterface $day): string => Carbon::instance($day)->translatedFormat('D, d M'));

        return [
            'datasets' => [
                [
                    'label' => 'Peminjaman',
                    'data' => $loanData->values()->toArray(),
                    'backgroundColor' => 'rgba(99, 102, 241, 0.7)',
                    'borderColor' => 'rgb(99, 102, 241)',
                    'borderWidth' => 2,
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Kunjungan',
                    'data' => $visitorData->values()->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.7)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}
