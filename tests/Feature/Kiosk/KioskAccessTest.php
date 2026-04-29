<?php

use App\Models\Setting;
use App\Support\Kiosk\KioskPinManager;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Setting::query()->create([
        'section' => 'kiosk',
        'key' => 'pin_hash',
        'value' => Hash::make('123456'),
    ]);
});

test('kiosk only requires a valid pin before showing the landing page', function () {
    $this->get(route('kiosk.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('kiosk/index')
            ->where('step', 'pin')
            ->has('visitorTypeOptions')
            ->has('purposeOptions'),
        );

    $this->post(route('kiosk.pin.store'), [
        'pin' => '123456',
    ])->assertRedirect(route('kiosk.index'));

    $this->get(route('kiosk.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('kiosk/index')
            ->where('step', 'ready')
            ->where('activeMenu', 'landing'),
        );
});

test('rotating kiosk sessions requires the pin again', function () {
    $this->post(route('kiosk.pin.store'), [
        'pin' => '123456',
    ])->assertRedirect(route('kiosk.index'));

    app(KioskPinManager::class)->rotateSessions();

    $this->get(route('kiosk.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('kiosk/index')
            ->where('step', 'pin'),
        );
});
