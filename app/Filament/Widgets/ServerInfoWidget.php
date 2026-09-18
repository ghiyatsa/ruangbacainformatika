<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\WhatsAppGateway;
use App\Support\AppTimezone;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Str;

class ServerInfoWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $environment = str(config('app.env', 'production'))->upper()->value();
        $isDebug = (bool) config('app.debug');
        $databaseConnection = strval(config('database.default', 'mysql'));
        $databaseDriver = strval(config("database.connections.{$databaseConnection}.driver", $databaseConnection));
        $cacheDriver = strval(config('cache.default', 'file'));
        $queueDriver = strval(config('queue.default', 'sync'));
        $timezone = AppTimezone::displayTimezone();
        $serverTime = AppTimezone::now()->translatedFormat('d M Y H:i');

        $diskTotal = $this->resolveDiskTotal();
        $diskFree = $this->resolveDiskFree();
        $diskUsed = max($diskTotal - $diskFree, 0);
        $diskUsagePercent = $diskTotal > 0
            ? (int) round(($diskUsed / $diskTotal) * 100)
            : 0;

        return [
            Stat::make('Mode Aplikasi', $environment)
                ->description($isDebug ? 'Debug aktif' : null)
                ->descriptionColor($isDebug ? 'warning' : null)
                ->descriptionIcon($isDebug ? Heroicon::OutlinedExclamationTriangle : null)
                ->color('primary')
                ->icon(Heroicon::OutlinedShieldCheck),

            Stat::make('Runtime', 'PHP '.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION)
                ->description('Laravel '.app()->version())
                ->descriptionColor('gray')
                ->descriptionIcon(Heroicon::OutlinedCodeBracket)
                ->color('info')
                ->icon(Heroicon::OutlinedCpuChip),

            Stat::make('Driver Layanan', "{$databaseDriver} / {$queueDriver}")
                ->description("Cache {$cacheDriver}")
                ->descriptionColor('gray')
                ->descriptionIcon(Heroicon::OutlinedCircleStack)
                ->color('info')
                ->icon(Heroicon::OutlinedServerStack),

            Stat::make('Penyimpanan', $this->formatBytes($diskFree).' bebas')
                ->description($this->formatBytes($diskTotal)." total ({$diskUsagePercent}%)")
                ->descriptionColor($diskUsagePercent >= 85 ? 'danger' : ($diskUsagePercent >= 70 ? 'warning' : 'success'))
                ->descriptionIcon(Heroicon::OutlinedArchiveBox)
                ->color('primary')
                ->icon(Heroicon::OutlinedArchiveBox),

            Stat::make('Waktu Server', $serverTime)
                ->description($timezone)
                ->descriptionColor('gray')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color('gray')
                ->icon(Heroicon::OutlinedClock),

            $this->gatewayStat(),
        ];
    }

    /**
     * Status gateway pesan, dahulu widget terpisah pada tab ini.
     * Digabung agar enam kartu mengisi tepat dua baris tiga kolom.
     */
    protected function gatewayStat(): Stat
    {
        /** @var WhatsAppGateway $gateway */
        $gateway = app(WhatsAppGateway::class);
        $status = $gateway->deviceStatus();

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

    protected function resolveDiskFree(): int
    {
        return max((int) disk_free_space(base_path()), 0);
    }

    protected function resolveDiskTotal(): int
    {
        return max((int) disk_total_space(base_path()), 0);
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return number_format($value, $power === 0 ? 0 : 1).' '.$units[$power];
    }
}
