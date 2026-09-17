<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Skripsis\SkripsiResource;
use App\Models\SimilaritySyncStatus;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class SimilaritySyncOverviewWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected int|array|null $columns = 2;

    protected static ?int $sort = 5;

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
        $failedCount = $counts['failed'];

        return [
            Stat::make('Dalam Antrean', $pendingCount)
                ->description($pendingCount > 0 ? 'Sedang dalam proses antrean' : 'Antrean kosong')
                ->descriptionIcon($pendingCount > 0 ? Heroicon::OutlinedArrowPath : Heroicon::OutlinedPauseCircle, IconPosition::Before)
                ->color($pendingCount > 0 ? 'warning' : 'gray')
                ->icon(Heroicon::OutlinedArrowPath)
                ->url($this->skripsisUrl([
                    'perlu_sync' => ['isActive' => true],
                ])),
            Stat::make('Sinkron Gagal', $failedCount)
                ->description($failedCount > 0 ? 'Perlu ditinjau ulang' : 'Tidak ada kegagalan')
                ->descriptionIcon($failedCount > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle, IconPosition::Before)
                ->color($failedCount > 0 ? 'danger' : 'gray')
                ->icon(Heroicon::OutlinedExclamationTriangle)
                ->url($this->skripsisUrl([
                    'sinkron_gagal' => ['isActive' => true],
                ])),
        ];
    }

    /**
     * Ringkasan jumlah karya per status sinkronisasi.
     *
     * Hasilnya disimpan sebentar karena satu pemuatan dashboard dapat
     * memanggil ini lebih dari sekali (mis. saat polling berjalan).
     *
     * @return array{pending: int, failed: int}
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
     * @return array{pending: int, failed: int}
     */
    protected function computeSummaryCounts(): array
    {
        $statusCounts = SimilaritySyncStatus::query()
            ->forExistingRecords()
            ->selectRaw('status, COUNT(*) AS jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return [
            'pending' => (int) ($statusCounts[SimilaritySyncStatus::STATUS_PENDING] ?? 0)
                + (int) ($statusCounts[SimilaritySyncStatus::STATUS_SYNCING] ?? 0),
            'failed' => (int) ($statusCounts[SimilaritySyncStatus::STATUS_FAILED] ?? 0),
        ];
    }
}
