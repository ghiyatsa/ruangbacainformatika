<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\WhatsAppMessageLogs\WhatsAppMessageLogsResource;
use App\Models\WhatsAppMessageLog;
use App\Services\WhatsAppGateway;
use App\Support\AppTimezone;
use Filament\Widgets\Widget;
use Illuminate\Support\Str;

class WhatsAppGatewayStatusWidget extends Widget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = ['xl' => 2];

    protected ?string $pollingInterval = '60s';

    public function getView(): string
    {
        return 'filament.widgets.whats-app-gateway-status-widget';
    }

    public function getViewData(): array
    {
        /** @var WhatsAppGateway $gateway */
        $gateway = app(WhatsAppGateway::class);
        $status = $gateway->deviceStatus();

        $configured = (bool) ($status['configured'] ?? false);
        $connected = (bool) ($status['connected'] ?? false);
        $reason = (string) ($status['reason'] ?? '');
        $device = (string) ($status['device'] ?? '');

        $sentCount = $this->todayLogCount(WhatsAppMessageLog::StatusSent);
        $failedCount = $this->todayLogCount(WhatsAppMessageLog::StatusFailed);
        $logsUrl = WhatsAppMessageLogsResource::getUrl('index');

        if (! $configured) {
            $connectionLabel = 'Belum Dikonfigurasi';
            $connectionDescription = 'Isi URL & token di Pengaturan Integrasi';
            $connectionColor = 'gray';
            $connectionIcon = 'heroicon-o-exclamation-triangle';
        } elseif ($connected) {
            $connectionLabel = 'Terhubung';
            $connectionDescription = $device ? "Perangkat {$device}" : 'Perangkat siap mengirim pesan';
            $connectionColor = 'success';
            $connectionIcon = 'heroicon-o-signal';
        } else {
            $connectionLabel = 'Terputus';
            $connectionDescription = Str::limit($reason ?: 'Tidak dapat memverifikasi perangkat.', 80);
            $connectionColor = 'danger';
            $connectionIcon = 'heroicon-o-signal-slash';
        }

        return [
            'connectionLabel' => $connectionLabel,
            'connectionDescription' => $connectionDescription,
            'connectionColor' => $connectionColor,
            'connectionIcon' => $connectionIcon,
            'connectionTitle' => $reason,
            'sentCount' => $sentCount,
            'failedCount' => $failedCount,
            'logsUrl' => $logsUrl,
        ];
    }

    protected function todayLogCount(string $status): int
    {
        return WhatsAppMessageLog::query()
            ->where('status', $status)
            ->where('created_at', '>=', AppTimezone::now()->startOfDay())
            ->count();
    }
}
