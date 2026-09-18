<?php

use App\Models\Setting;
use App\Support\KioskIdlePolicy;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;

/**
 * Uji infrastruktur kiosk yang masih hidup di API (klien Flutter desktop).
 *
 * Cakupan ini dipindahkan dari `tests/Feature/Kiosk/KioskAccessTest.php` yang
 * menguji route web `/kiosk/*`. Rate limiter dan `KioskIdlePolicy` tetap
 * dipakai endpoint `/api/kiosk/*`, jadi pengamannya harus tetap ada.
 */
beforeEach(function () {
    Setting::query()->updateOrCreate(
        ['section' => 'kiosk', 'key' => 'operating_open_time'],
        ['value' => '08:00'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'kiosk', 'key' => 'operating_close_time'],
        ['value' => '17:00'],
    );
});

afterEach(function () {
    Carbon::setTestNow();
});

it('registers kiosk rate limiters with lobby-safe thresholds', function () {
    $request = Request::create('/api/kiosk/bootstrap', 'GET', server: [
        'REMOTE_ADDR' => '127.0.0.1',
    ]);

    $rateLimiter = app(RateLimiter::class);
    $pinLimiter = $rateLimiter->limiter('kiosk-pin');
    $bookSearchLimiter = $rateLimiter->limiter('kiosk-book-search');
    $memberLookupLimiter = $rateLimiter->limiter('kiosk-member-lookup');
    $memberStatusLimiter = $rateLimiter->limiter('kiosk-member-status');
    $submitLimiter = $rateLimiter->limiter('kiosk-submit');

    expect($pinLimiter)->not->toBeNull()
        ->and($bookSearchLimiter)->not->toBeNull()
        ->and($memberLookupLimiter)->not->toBeNull()
        ->and($memberStatusLimiter)->not->toBeNull()
        ->and($submitLimiter)->not->toBeNull();

    $pinLimit = $pinLimiter($request);
    $bookSearchLimit = $bookSearchLimiter($request);
    $memberStatusLimit = $memberStatusLimiter($request);

    expect($pinLimit->maxAttempts)->toBe(5)
        ->and($pinLimit->decaySeconds)->toBe(300)
        ->and($bookSearchLimit->maxAttempts)->toBe(180)
        ->and($memberStatusLimit->maxAttempts)->toBe(180);
});

it('keeps verified sessions active during operating hours without idle expiry', function () {
    Carbon::setTestNow('2026-06-07 03:00:00'); // 10:00 WIB

    expect(app(KioskIdlePolicy::class)->isSessionStillActive(Carbon::parse('2026-06-07 01:01:00')))
        ->toBeTrue();
});

it('allows a session to start inside operating hours only', function () {
    Carbon::setTestNow('2026-06-07 03:00:00'); // 10:00 WIB — buka

    expect(app(KioskIdlePolicy::class)->canStartSession())->toBeTrue();

    Carbon::setTestNow('2026-06-07 15:00:00'); // 22:00 WIB — tutup

    expect(app(KioskIdlePolicy::class)->canStartSession())->toBeFalse();
});
