<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Console\Commands\PruneTemporaryRecordsCommand;
use App\Console\Commands\RemindReturnCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::command(RemindReturnCommand::class)->dailyAt('08:00');
Schedule::command(PruneTemporaryRecordsCommand::class)
    ->dailyAt('02:00')
    ->withoutOverlapping();
