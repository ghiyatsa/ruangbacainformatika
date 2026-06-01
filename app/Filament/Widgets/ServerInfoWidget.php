<?php

namespace App\Filament\Widgets;

use App\Support\AppTimezone;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

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
                ->description($isDebug ? 'Debug aktif' : 'Debug dimatikan')
                ->descriptionIcon($isDebug ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle)
                ->color($isDebug ? 'warning' : 'success')
                ->icon(Heroicon::OutlinedShieldCheck),

            Stat::make('Runtime', 'PHP '.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION)
                ->description('Laravel '.app()->version())
                ->descriptionIcon(Heroicon::OutlinedCodeBracket)
                ->color('info')
                ->icon(Heroicon::OutlinedCpuChip),

            Stat::make('Driver Layanan', "{$databaseDriver} / {$queueDriver}")
                ->description("Cache {$cacheDriver}")
                ->descriptionIcon(Heroicon::OutlinedCircleStack)
                ->color('primary')
                ->icon(Heroicon::OutlinedServerStack),

            Stat::make('Penyimpanan', $this->formatBytes($diskFree).' bebas')
                ->description($this->formatBytes($diskTotal)." total, {$diskUsagePercent}% terpakai")
                ->descriptionIcon(Heroicon::OutlinedArchiveBox)
                ->color($diskUsagePercent >= 85 ? 'danger' : ($diskUsagePercent >= 70 ? 'warning' : 'success'))
                ->icon(Heroicon::OutlinedArchiveBox),

            Stat::make('Waktu Server', $serverTime)
                ->description("Zona waktu {$timezone}")
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color('gray')
                ->icon(Heroicon::OutlinedClock),
        ];
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
