<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use App\Models\VisitLog;
use App\Support\AppTimezone;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;

class LoanActivityChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Aktivitas Mingguan';

    protected ?string $description = 'Tren peminjaman dan kunjungan tamu kiosk 7 hari terakhir.';

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
        [$loanData, $visitorData, $labels] = $this->resolveSeries();

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

    /**
     * Deret data per hari untuk kedua dataset.
     *
     * @return array{0: Collection<int, int>, 1: Collection<int, int>, 2: Collection<int, string>}
     */
    protected function resolveSeries(): array
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

        return [$loanData, $visitorData, $labels];
    }

    /**
     * Lantai skala sumbu Y.
     *
     * Bila nilai tertinggi data hanya 1, Chart.js menskalakan sumbu ke 0-1
     * sehingga satu bar memenuhi seluruh tinggi grafik. Lantai ini menjaga
     * tinggi bar tetap proporsional terhadap besaran datanya. Dihitung
     * mandiri agar tidak bergantung pada urutan pemanggilan getData().
     */
    protected function yAxisMax(): int
    {
        [$loanData, $visitorData] = $this->resolveSeries();

        $peak = max(1, (int) $loanData->max(), (int) $visitorData->max());

        return match (true) {
            $peak <= 1 => 5,
            $peak <= 5 => 10,
            $peak <= 10 => 20,
            $peak <= 50 => (int) (ceil($peak / 10) * 10),
            default => (int) (ceil($peak / 50) * 50),
        };
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
                    'suggestedMax' => $this->yAxisMax(),
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
