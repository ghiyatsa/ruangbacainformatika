<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use App\Models\VisitLog;
use App\Support\AppTimezone;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class LoanActivityChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Aktivitas 7 Hari Terakhir';

    protected ?string $description = 'Tren peminjaman dan kunjungan tamu kiosk.';

    protected int|string|array $columnSpan = 'full';

    /**
     * Tinggi wadah ditentukan lewat rasio aspek, bukan max-height.
     * Memakai max-height membuat Filament mematikan rasio aspek namun
     * canvas tidak diberi tinggi pasti, sehingga grafik tampak terpotong.
     *
     * Penghitungan per hari memakai rentang waktu, bukan CONVERT_TZ,
     * karena tabel zona waktu MySQL tidak selalu terisi di server
     * produksi dan CONVERT_TZ akan mengembalikan NULL.
     */
    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(
            fn (int $daysAgo): \DateTimeInterface => now(VisitLog::adminTimezone())->subDays($daysAgo)->startOfDay(),
        );

        $loanData = $days->map(function (\DateTimeInterface $day): int {
            [$startOfDay, $endOfDay] = AppTimezone::dayRange($day);

            return Loan::query()
                ->whereBetween('borrowed_at', [$startOfDay, $endOfDay])
                ->count('*');
        });

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
                    'maxBarThickness' => 48,
                ],
                [
                    'label' => 'Kunjungan Tamu',
                    'data' => $visitorData->values()->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.7)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'borderRadius' => 4,
                    'maxBarThickness' => 48,
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
            'maintainAspectRatio' => true,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
