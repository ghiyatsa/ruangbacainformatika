<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Skripsis\SkripsiResource;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SimilaritySyncOverviewWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Sinkronisasi Similarity';

    protected ?string $description = 'Status sinkronisasi skripsi.';

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function skripsisUrl(array $filters = []): string
    {
        return SkripsiResource::getUrl('index', [
            'tableFilters' => $filters,
        ]);
    }

    protected function getStats(): array
    {
        $totalSkripsi = Skripsi::query()->count();
        $syncedCount = SimilaritySyncStatus::query()
            ->where('status', SimilaritySyncStatus::STATUS_SYNCED)
            ->count();
        $failedCount = SimilaritySyncStatus::query()
            ->where('status', SimilaritySyncStatus::STATUS_FAILED)
            ->count();
        $pendingCount = SimilaritySyncStatus::query()
            ->whereIn('status', [
                SimilaritySyncStatus::STATUS_PENDING,
                SimilaritySyncStatus::STATUS_SYNCING,
            ])
            ->count();
        $unscheduledCount = Skripsi::query()
            ->whereDoesntHave('similaritySyncStatus')
            ->count();

        return [
            Stat::make('Sinkron Berhasil', $syncedCount)
                ->description($totalSkripsi > 0 ? "{$totalSkripsi} total skripsi" : 'Belum ada data')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle, IconPosition::Before)
                ->color('success')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->url($this->skripsisUrl([
                    'similarity_status' => ['value' => SimilaritySyncStatus::STATUS_SYNCED],
                ])),
            Stat::make('Perlu Tindak Lanjut', $failedCount)
                ->description($failedCount > 0 ? 'Ada sinkronisasi gagal.' : 'Tidak ada error.')
                ->descriptionIcon($failedCount > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle, IconPosition::Before)
                ->color($failedCount > 0 ? 'danger' : 'success')
                ->icon(Heroicon::OutlinedExclamationTriangle)
                ->url($this->skripsisUrl([
                    'similarity_status' => ['value' => SimilaritySyncStatus::STATUS_FAILED],
                ])),
            Stat::make('Sedang Diproses', $pendingCount)
                ->description($pendingCount > 0 ? 'Antrean masih berjalan.' : 'Tidak ada antrean.')
                ->descriptionIcon($pendingCount > 0 ? Heroicon::OutlinedArrowPath : Heroicon::OutlinedPauseCircle, IconPosition::Before)
                ->color($pendingCount > 0 ? 'warning' : 'gray')
                ->icon(Heroicon::OutlinedArrowPath)
                ->url($this->skripsisUrl([
                    'perlu_sync' => ['isActive' => true],
                ])),
            Stat::make('Belum Dijadwalkan', $unscheduledCount)
                ->description($unscheduledCount > 0 ? 'Masih ada yang belum dijadwalkan.' : 'Semua sudah terlacak.')
                ->descriptionIcon($unscheduledCount > 0 ? Heroicon::OutlinedClock : Heroicon::OutlinedCheckCircle, IconPosition::Before)
                ->color($unscheduledCount > 0 ? 'info' : 'gray')
                ->icon(Heroicon::OutlinedClock)
                ->url($this->skripsisUrl([
                    'belum_dijadwalkan' => ['isActive' => true],
                ])),
        ];
    }
}
