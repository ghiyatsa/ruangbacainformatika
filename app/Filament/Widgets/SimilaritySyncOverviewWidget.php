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

        $totalDoc = $counts['total_doc'];
        $syncedCount = $counts['synced'];
        $failedCount = $counts['failed'];
        $pendingCount = $counts['pending'];
        $unscheduledCount = $counts['unscheduled'];

        return [
            Stat::make('Sinkron Berhasil', $syncedCount)
                ->description($totalDoc > 0 ? "{$syncedCount} dari {$totalDoc} karya terindeks" : 'Belum ada data karya')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle, IconPosition::Before)
                ->color('success')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->url($this->skripsisUrl([
                    'similarity_status' => ['value' => SimilaritySyncStatus::STATUS_SYNCED],
                ])),
            Stat::make('Sinkron Gagal', $failedCount)
                ->description($failedCount > 0 ? 'Perlu sinkronisasi ulang' : 'Tidak ada kendala')
                ->descriptionIcon($failedCount > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle, IconPosition::Before)
                ->color($failedCount > 0 ? 'danger' : 'success')
                ->icon(Heroicon::OutlinedExclamationTriangle)
                ->url($this->skripsisUrl([
                    'similarity_status' => ['value' => SimilaritySyncStatus::STATUS_FAILED],
                ])),
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
     * @return array{total_doc: int, synced: int, failed: int, pending: int, unscheduled: int}
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
     * @return array{total_doc: int, synced: int, failed: int, pending: int, unscheduled: int}
     */
    protected function computeSummaryCounts(): array
    {
        $totalSkripsi = Skripsi::query()->count();
        $totalInternship = InternshipReport::query()->count();

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
            'total_doc' => $totalSkripsi + $totalInternship,
            'synced' => (int) ($statusCounts[SimilaritySyncStatus::STATUS_SYNCED] ?? 0),
            'failed' => (int) ($statusCounts[SimilaritySyncStatus::STATUS_FAILED] ?? 0),
            'pending' => (int) ($statusCounts[SimilaritySyncStatus::STATUS_PENDING] ?? 0)
                + (int) ($statusCounts[SimilaritySyncStatus::STATUS_SYNCING] ?? 0),
            'unscheduled' => $unscheduledCount,
        ];
    }
}
