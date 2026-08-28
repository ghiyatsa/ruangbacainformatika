<?php

use App\Models\KioskDevice;
use Illuminate\Support\Carbon;

use function Pest\Laravel\artisan;

it('prunes stale kiosk devices', function () {
    Carbon::setTestNow('2026-05-29 09:00:00');

    $staleDevice = KioskDevice::query()->create([
        'session_id' => 'stale-session',
        'device_token' => str_repeat('a', 64),
        'ip_address' => '192.168.1.10',
        'last_active_at' => now()->subDays(40),
    ]);
    $activeDevice = KioskDevice::query()->create([
        'session_id' => 'active-session',
        'device_token' => str_repeat('b', 64),
        'ip_address' => '192.168.1.11',
        'last_active_at' => now()->subDays(3),
    ]);

    artisan('app:prune-temporary-records --device-days=30')
        ->assertSuccessful();

    expect(KioskDevice::query()->whereKey($staleDevice->id)->exists())->toBeFalse()
        ->and(KioskDevice::query()->whereKey($activeDevice->id)->exists())->toBeTrue();
});
