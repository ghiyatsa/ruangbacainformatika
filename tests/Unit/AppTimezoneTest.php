<?php

use App\Support\AppTimezone;
use Illuminate\Support\Carbon;

it('formats datetimes in the application display timezone', function () {
    $dateTime = Carbon::parse('2026-05-28 17:30:00', 'UTC');

    expect(AppTimezone::format($dateTime, 'd F Y H:i'))
        ->toBe('29 May 2026 00:30');
});

it('builds day ranges using the application display timezone', function () {
    Carbon::setTestNow(Carbon::parse('2026-05-29 00:30:00', AppTimezone::displayTimezone()));

    [$startOfDay, $endOfDay] = AppTimezone::dayRange();

    expect($startOfDay->format('Y-m-d H:i:s'))->toBe('2026-05-28 17:00:00')
        ->and($endOfDay->format('Y-m-d H:i:s'))->toBe('2026-05-29 16:59:59');

    Carbon::setTestNow();
});
