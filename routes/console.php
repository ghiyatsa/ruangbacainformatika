<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Console\Commands\KioskSyncPullCommand;
use App\Console\Commands\KioskSyncPushCommand;
use App\Console\Commands\RemindReturnCommand;
use Illuminate\Support\Facades\Schedule;

// 1. Pengingat Pengembalian Buku Pagi
Schedule::command(RemindReturnCommand::class)->dailyAt('08:00');

// 2. Pembersihan Rutin Harian (Dijalankan berurutan sekali jalan pukul 03:00)
Schedule::call(function (): void {
    Artisan::call('app:prune-temporary-records');
    Artisan::call('app:prune-notifications');
    Artisan::call('app:prune-whatsapp-logs');
    Artisan::call('app:prune-search-history');
    Artisan::call('app:prune-audit-logs');
    Artisan::call('queue:prune-failed');
})->dailyAt('03:00')->name('daily:cleanup');

// 3. Sinkronisasi Kiosk Hibrida (Hanya jika instance berperan sebagai `edge`)
Schedule::command(KioskSyncPushCommand::class)
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->when(fn (): bool => config('app.app.kiosk.role') === 'edge');

Schedule::command(KioskSyncPullCommand::class)
    ->hourly()
    ->withoutOverlapping()
    ->when(fn (): bool => config('app.app.kiosk.role') === 'edge');
