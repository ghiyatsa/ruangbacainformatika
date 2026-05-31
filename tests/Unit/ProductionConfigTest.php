<?php

use App\Support\AppTimezone;

it('uses the application display timezone for scheduled tasks', function () {
    expect(config('app.schedule_timezone'))->toBe(AppTimezone::displayTimezone());
});

it('defaults session cookies to secure in production environments', function () {
    $originalAppEnv = getenv('APP_ENV');
    $originalSessionSecureCookie = getenv('SESSION_SECURE_COOKIE');

    putenv('APP_ENV=production');
    putenv('SESSION_SECURE_COOKIE');

    unset($_ENV['APP_ENV'], $_ENV['SESSION_SECURE_COOKIE']);
    unset($_SERVER['APP_ENV'], $_SERVER['SESSION_SECURE_COOKIE']);

    $sessionConfig = require config_path('session.php');

    expect($sessionConfig['secure'])->toBeTrue();

    if ($originalAppEnv === false) {
        putenv('APP_ENV');
        unset($_ENV['APP_ENV'], $_SERVER['APP_ENV']);
    } else {
        putenv("APP_ENV={$originalAppEnv}");
        $_ENV['APP_ENV'] = $originalAppEnv;
        $_SERVER['APP_ENV'] = $originalAppEnv;
    }

    if ($originalSessionSecureCookie === false) {
        putenv('SESSION_SECURE_COOKIE');
        unset($_ENV['SESSION_SECURE_COOKIE'], $_SERVER['SESSION_SECURE_COOKIE']);
    } else {
        putenv("SESSION_SECURE_COOKIE={$originalSessionSecureCookie}");
        $_ENV['SESSION_SECURE_COOKIE'] = $originalSessionSecureCookie;
        $_SERVER['SESSION_SECURE_COOKIE'] = $originalSessionSecureCookie;
    }
});
