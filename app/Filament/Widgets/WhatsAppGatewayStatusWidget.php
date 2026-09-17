<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\WhatsAppMessageLogs\WhatsAppMessageLogsResource;
use App\Models\WhatsAppMessageLog;
use App\Services\WhatsAppGateway;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Str;

class WhatsAppGatewayStatusWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        /** @var WhatsAppGateway $gateway */
        $gateway = app(WhatsAppGateway::class);
        $status = $gateway->deviceStatus();

        // Satu kueri agregat untuk kedua kartu, bukan dua COUNT terpisah.
        $todayCounts = $this->todayStatusCounts();

        return [
            $this->connectionStat($status),
            $this->todaySentStat($todayCounts[WhatsAppMessageLog::StatusSent] ?? 0),
            $this->todayFailedStat($todayCounts[WhatsAppMessageLog::StatusFailed] ?? 0),
        ];
    }

    /**
     * @param  array<string, mixed>  $status
     */
    protected function connectionStat(array $status): Stat
    {
        $configured = (bool) ($status['configured'] ?? false);
        $connected = (bool) ($status['connected'] ?? false);

        if (! $configured) {
            return Stat::make('WhatsApp Gateway', 'Belum Dikonfigurasi')
                ->description('Isi URL & token di Pengaturan Integrasi')
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle, IconPosition::Before)
                ->color('gray')
                ->icon(Heroicon::OutlinedChatBubbleLeftRight);
        }

        if ($connected) {
            $device = $status['device'] ?? null;

            return Stat::make('WhatsApp Gateway', 'Terhubung')
                ->description($device ? "Perangkat {$device}" : 'Perangkat siap mengirim pesan')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle, IconPosition::Before)
                ->color('success')
                ->icon(Heroicon::OutlinedSignal);
        }

        $reason = (string) ($status['reason'] ?? 'Tidak dapat memverifikasi perangkat.');

        return Stat::make('WhatsApp Gateway', 'Terputus')
            ->description(Str::limit($reason, 60))
            ->descriptionIcon(Heroicon::OutlinedExclamationTriangle, IconPosition::Before)
            ->color('danger')
            ->icon(Heroicon::OutlinedSignalSlash)
            ->extraAttributes(['title' => $reason]);
    }

    protected function todaySentStat(int $count): Stat
    {
        return Stat::make('Terkirim Hari Ini', (string) $count)
            ->description($count > 0 ? 'Berhasil dikirim melalui gateway' : 'Belum ada pengiriman')
            ->descriptionIcon(Heroicon::OutlinedCheckCircle, IconPosition::Before)
            ->color($count > 0 ? 'success' : 'gray')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->url(WhatsAppMessageLogsResource::getUrl('index'));
    }

    protected function todayFailedStat(int $count): Stat
    {
        return Stat::make('Gagal Hari Ini', (string) $count)
            ->description($count > 0 ? 'Perlu pengecekan gateway' : 'Tidak ada kegagalan')
            ->descriptionIcon($count > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle, IconPosition::Before)
            ->color($count > 0 ? 'danger' : 'success')
            ->icon(Heroicon::OutlinedExclamationCircle)
            ->url(WhatsAppMessageLogsResource::getUrl('index'));
    }

    /**
     * Jumlah log hari ini per status dalam satu kueri.
     *
     * @return array<string, int>
     */
    protected function todayStatusCounts(): array
    {
        return WhatsAppMessageLog::query()
            ->where('created_at', '>=', now()->utc()->startOfDay())
            ->selectRaw('status, COUNT(*) AS jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status')
            ->map(fn ($jumlah): int => (int) $jumlah)
            ->all();
    }
}
