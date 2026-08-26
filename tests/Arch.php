<?php

arch('debug helpers are not used in application code')
    ->expect(['dd', 'dump', 'var_dump', 'ray', 'ddd'])
    ->not->toBeUsedIn('App');

arch('no hardcoded env calls outside config and service providers')
    ->expect('env')
    ->toOnlyBeUsedIn([
        'config',
        'App\Providers',
        'database',
        'tests',
    ]);

arch('models do not depend on http, view, queue, or filament layers')
    ->expect('App\Models')
    ->not->toUse([
        'Illuminate\Http',
        'Illuminate\view',
        'Illuminate\Contracts\Queue',
        'Illuminate\Support\Facades\Queue',
        'Filament',
        'Inertia',
        'App\Filament',
        'App\Notifications\WhatsAppOtpNotification',
    ])
    // User adalah kontrak wajib panel Filament (FilamentUser + HasAvatar).
    ->ignoring('App\Models\User');
