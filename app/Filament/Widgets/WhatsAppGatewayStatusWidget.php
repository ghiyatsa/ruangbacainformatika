<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\WhatsAppMessageLogs\WhatsAppMessageLogsResource;
use App\Models\WhatsAppMessageLog;
use App\Services\WhatsAppGateway;
use App\Support\AppTimezone;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Str;

class WhatsAppGatewayStatusWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = ['xl' => 2];

    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        /** @var WhatsAppGateway $gateway */
        $gateway = app(WhatsAppGateway::class);
        $status = $gateway->deviceStatus();

        return [
            $this->connectionStat($status),
            $this->todaySentStat(),
            $this->todayFailedStat(),
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
            ->extraAttributes([
                'title' => $reason,
            ]);
    }

    protected function todaySentStat(): Stat
    {
        $count = $this->todayLogCount(WhatsAppMessageLog::StatusSent);

        return Stat::make('Pesan Terkirim Hari Ini', (string) $count)
            ->description($count > 0 ? 'Berhasil dikirim melalui gateway' : 'Belum ada pengiriman')
            ->descriptionIcon(Heroicon::OutlinedCheckCircle, IconPosition::Before)
            ->color($count > 0 ? 'success' : 'gray')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->url(WhatsAppMessageLogsResource::getUrl('index'));
    }

    protected function todayFailedStat(): Stat
    {
        $count = $this->todayLogCount(WhatsAppMessageLog::StatusFailed);

        return Stat::make('Pesan Gagal Hari Ini', (string) $count)
            ->description($count > 0 ? 'Perlu pengecekan gateway' : 'Tidak ada kegagalan')
            ->descriptionIcon($count > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle, IconPosition::Before)
            ->color($count > 0 ? 'danger' : 'success')
            ->icon(Heroicon::OutlinedExclamationCircle)
            ->url(WhatsAppMessageLogsResource::getUrl('index'));
    }

    protected function todayLogCount(string $status): int
    {
        return WhatsAppMessageLog::query()
            ->where('status', $status)
            ->where('created_at', '>=', AppTimezone::now()->startOfDay())
            ->count();
    }
}
