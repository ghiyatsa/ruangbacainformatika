<?php

use App\Models\Setting;
use Database\Seeders\AppSettingSeeder;

it('seeds integration settings from env-backed config defaults', function () {
    config()->set('services.similarity_api.url', 'https://similarity-env.test');
    config()->set('services.similarity_api.secret', 'seeded-secret-from-env');
    config()->set('services.similarity_api.timeout', 25);
    config()->set('services.fonnte.url', 'https://api.fonnte.com/send');
    config()->set('services.fonnte.token', 'seeded-fonnte-token');

    app(AppSettingSeeder::class)->run();

    expect(Setting::query()->where('section', 'integration')->where('key', 'similarity_api_url')->value('value'))
        ->toBe('https://similarity-env.test')
        ->and(decrypt((string) Setting::query()->where('section', 'integration')->where('key', 'similarity_api_secret')->value('value')))
        ->toBe('seeded-secret-from-env')
        ->and(Setting::query()->where('section', 'integration')->where('key', 'similarity_api_timeout')->value('value'))
        ->toBe('25')
        ->and(Setting::query()->where('section', 'integration')->where('key', 'whatsapp_api_url')->value('value'))
        ->toBe('https://api.fonnte.com/send')
        ->and(Setting::query()->where('section', 'integration')->where('key', 'whatsapp_api_token')->value('value'))
        ->toBe('seeded-fonnte-token');
});
