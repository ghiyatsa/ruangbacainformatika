<?php

use App\Console\Commands\RemindReturnCommand;
use App\Support\AppTimezone;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Seluruh jadwal memakai zona waktu tampilan (Asia/Jakarta), bukan app.timezone (UTC).
// Tanpa ini, dailyAt('08:00') dieksekusi 08:00 UTC = 15:00 WIB.
$scheduleTimezone = AppTimezone::displayTimezone();

// 1. Pengingat Pengembalian Buku Pagi (08:00 WIB)
Schedule::command(RemindReturnCommand::class)
    ->dailyAt('08:00')
    ->timezone($scheduleTimezone);

// 2. Pembersihan Rutin Harian (03:00 WIB)
Schedule::call(function (): void {
    Artisan::call('app:prune-temporary-records');
    Artisan::call('app:prune-notifications');
    Artisan::call('app:prune-whatsapp-logs');
    Artisan::call('app:prune-search-history');
    Artisan::call('app:prune-audit-logs');
    Artisan::call('queue:prune-failed');
})
    ->dailyAt('03:00')
    ->timezone($scheduleTimezone)
    ->name('daily:cleanup');
