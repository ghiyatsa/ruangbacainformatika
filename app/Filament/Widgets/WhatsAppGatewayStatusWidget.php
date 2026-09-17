<?php

namespace App\Filament\Widgets;

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

    /**
     * Tanpa polling. Status perangkat dimuat sekali saat halaman dibuka,
     * sehingga dashboard tidak menunggu jaringan berulang kali. Bila perlu
     * memuat ulang, pengelola dapat menyegarkan halaman.
     */
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        /** @var WhatsAppGateway $gateway */
        $gateway = app(WhatsAppGateway::class);

        return [
            $this->connectionStat($gateway->deviceStatus()),
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
            return Stat::make('Gateway Pesan', 'Belum Dikonfigurasi')
                ->description('Isi URL & token di Pengaturan Integrasi')
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle, IconPosition::Before)
                ->color('gray')
                ->icon(Heroicon::OutlinedChatBubbleLeftRight);
        }

        if ($connected) {
            $device = $status['device'] ?? null;

            return Stat::make('Gateway Pesan', 'Terhubung')
                ->description($device ? "Perangkat {$device}" : 'Perangkat siap mengirim pesan')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle, IconPosition::Before)
                ->color('success')
                ->icon(Heroicon::OutlinedSignal);
        }

        $reason = (string) ($status['reason'] ?? 'Tidak dapat memverifikasi perangkat.');

        return Stat::make('Gateway Pesan', 'Terputus')
            ->description(Str::limit($reason, 60))
            ->descriptionIcon(Heroicon::OutlinedExclamationTriangle, IconPosition::Before)
            ->color('danger')
            ->icon(Heroicon::OutlinedSignalSlash)
            ->extraAttributes(['title' => $reason]);
    }
}
