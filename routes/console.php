<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Console\Commands\PruneAuditLogsCommand;
use App\Console\Commands\PruneNotificationsCommand;
use App\Console\Commands\PruneSearchHistoryCommand;
use App\Console\Commands\PruneTemporaryRecordsCommand;
use App\Console\Commands\PruneWhatsAppLogsCommand;
use App\Console\Commands\RemindReturnCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::command(RemindReturnCommand::class)->dailyAt('08:00');
Schedule::command(PruneTemporaryRecordsCommand::class)
    ->dailyAt('02:00')
    ->withoutOverlapping();
Schedule::command(PruneNotificationsCommand::class)
    ->dailyAt('03:00')
    ->withoutOverlapping();
Schedule::command(PruneWhatsAppLogsCommand::class)
    ->dailyAt('03:30')
    ->withoutOverlapping();
Schedule::command(PruneSearchHistoryCommand::class)
    ->dailyAt('04:00')
    ->withoutOverlapping();
Schedule::command(PruneAuditLogsCommand::class)
    ->dailyAt('04:30')
    ->withoutOverlapping();
Schedule::command('queue:prune-failed')
    ->dailyAt('05:00');

Schedule::command('schedule-monitor:sync')
    ->everySixHours()
    ->withoutOverlapping();
Schedule::command('model:prune', ['--model' => config('schedule-monitor.models.monitored_scheduled_log_item')])
    ->dailyAt('05:30')
    ->withoutOverlapping();
