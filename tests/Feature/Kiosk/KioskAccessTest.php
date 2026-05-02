<?php

use App\Models\Setting;
use App\Support\Kiosk\KioskPinManager;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\instance;
use function Pest\Laravel\post;


beforeEach(function () {
    Setting::query()->create([
        'section' => 'kiosk',
        'key' => 'pin_hash',
        'value' => Hash::make('123456'),
    ]);
});

it('kiosk shows pin entry when not verified', function () {
    get(route('kiosk.index'))
        ->assertSuccessful()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('kiosk/index')
                ->where('step', 'pin')
                ->has('visitorTypeOptions')
                ->has('purposeOptions'),
        );
});

it('kiosk allows access after valid pin entry', function () {
    post(route('kiosk.pin.store'), [
        'pin' => '123456',
    ])->assertRedirect(route('kiosk.index'));

    assertDatabaseHas('kiosk_devices', [
        'ip_address' => '127.0.0.1',
    ]);

    $mock = Mockery::mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    get(route('kiosk.index'))
        ->assertSuccessful()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('kiosk/index')
                ->where('step', 'ready')
                ->where('activeMenu', 'landing'),
        );
});

it('rotating kiosk sessions requires the pin again', function () {
    $mock = Mockery::mock(KioskPinManager::class);
    $mock->shouldReceive('rotateSessions')->once()->andReturn(2);
    $mock->shouldReceive('isVerified')->andReturn(false);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    app(KioskPinManager::class)->rotateSessions();

    get(route('kiosk.index'))
        ->assertSuccessful()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('kiosk/index')
                ->where('step', 'pin'),
        );
});
