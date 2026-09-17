<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Skripsis\SkripsiResource;
use App\Models\InternshipReport;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class SimilaritySyncOverviewWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected int|array|null $columns = 2;

    protected static ?int $sort = 2;

    /**
     * Antrean sinkronisasi berubah karena proses latar, bukan karena
     * interaksi pengguna. Polling 10 detik menjalankan 10 kueri agregat
     * setiap 10 detik tanpa ada yang mengamati perubahannya; 60 detik
     * sudah cukup responsif dengan beban seperenamnya.
     */
    protected ?string $pollingInterval = '60s';

    protected int|string|array $columnSpan = 'full';

    /** Lama simpan ringkasan agar poll tidak selalu memukul basis data. */
    protected const CACHE_SECONDS = 60;

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function skripsisUrl(array $filters = []): string
    {
        return SkripsiResource::getUrl('index', [
            'filters' => $filters,
        ]);
    }

    protected function getStats(): array
    {
        $counts = $this->summaryCounts();

        $pendingCount = $counts['pending'];
        $unscheduledCount = $counts['unscheduled'];

        return [
            Stat::make('Dalam Antrean', $pendingCount)
                ->description($pendingCount > 0 ? 'Sedang dalam proses antrean' : 'Antrean kosong')
                ->descriptionIcon($pendingCount > 0 ? Heroicon::OutlinedArrowPath : Heroicon::OutlinedPauseCircle, IconPosition::Before)
                ->color($pendingCount > 0 ? 'warning' : 'gray')
                ->icon(Heroicon::OutlinedArrowPath)
                ->url($this->skripsisUrl([
                    'perlu_sync' => ['isActive' => true],
                ])),
            Stat::make('Belum Dijadwalkan', $unscheduledCount)
                ->description($unscheduledCount > 0 ? 'Karya belum masuk antrean' : 'Semua karya telah terjadwal')
                ->descriptionIcon($unscheduledCount > 0 ? Heroicon::OutlinedClock : Heroicon::OutlinedCheckCircle, IconPosition::Before)
                ->color($unscheduledCount > 0 ? 'info' : 'gray')
                ->icon(Heroicon::OutlinedClock)
                ->url($this->skripsisUrl([
                    'belum_dijadwalkan' => ['isActive' => true],
                ])),
        ];
    }

    /**
     * Ringkasan jumlah karya per status sinkronisasi.
     *
     * Hasilnya disimpan sebentar karena satu pemuatan dashboard dapat
     * memanggil ini lebih dari sekali (mis. saat polling berjalan).
     *
     * @return array{pending: int, unscheduled: int}
     */
    protected function summaryCounts(): array
    {
        return Cache::remember(
            'similarity-sync-overview:summary',
            now()->addSeconds(self::CACHE_SECONDS),
            fn (): array => $this->computeSummaryCounts(),
        );
    }

    /**
     * @return array{pending: int, unscheduled: int}
     */
    protected function computeSummaryCounts(): array
    {
        $statusCounts = SimilaritySyncStatus::query()
            ->forExistingRecords()
            ->selectRaw('status, COUNT(*) AS jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $unscheduledCount = Skripsi::query()
            ->whereDoesntHave('similaritySyncStatus')
            ->count() + InternshipReport::query()
            ->whereDoesntHave('similaritySyncStatus')
            ->count();

        return [
            'pending' => (int) ($statusCounts[SimilaritySyncStatus::STATUS_PENDING] ?? 0)
                + (int) ($statusCounts[SimilaritySyncStatus::STATUS_SYNCING] ?? 0),
            'unscheduled' => $unscheduledCount,
        ];
    }
}
