<?php

use App\Models\VisitLog;
use Illuminate\Support\Carbon;

it('converts visit timestamps to the admin timezone for display', function () {
    $visitLog = new VisitLog([
        'visited_at' => Carbon::parse('2026-05-29 00:15:00', 'UTC'),
    ]);

    expect($visitLog->visitedAtForAdmin()?->format('Y-m-d H:i:s'))
        ->toBe('2026-05-29 07:15:00');
});

it('builds the today range using the admin timezone while keeping utc storage boundaries', function () {
    Carbon::setTestNow(Carbon::parse('2026-05-29 00:30:00', VisitLog::adminTimezone()));

    [$startOfDay, $endOfDay] = VisitLog::adminDayRange();

    expect($startOfDay->format('Y-m-d H:i:s'))->toBe('2026-05-28 17:00:00')
        ->and($endOfDay->format('Y-m-d H:i:s'))->toBe('2026-05-29 16:59:59');

    Carbon::setTestNow();
});
