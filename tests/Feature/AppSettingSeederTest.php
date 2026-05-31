<?php

use App\Models\Setting;
use Database\Seeders\AppSettingSeeder;
use Illuminate\Support\Facades\Hash;

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

it('seeds env-backed global notice and kiosk pin defaults', function () {
    config()->set('app.seed_defaults.global_notice.enabled', true);
    config()->set('app.seed_defaults.global_notice.text', 'Perpustakaan tutup sementara pada hari Sabtu.');
    config()->set('app.seed_defaults.global_notice.url', 'https://example.com/pengumuman');
    config()->set('app.seed_defaults.global_notice.link_label', 'Lihat info');
    config()->set('app.seed_defaults.global_notice.tone', 'warning');
    config()->set('app.seed_defaults.kiosk.pin', '123456');

    app(AppSettingSeeder::class)->run();

    $kioskPinHash = (string) Setting::query()->where('section', 'kiosk')->where('key', 'pin_hash')->value('value');

    expect(Setting::query()->where('section', 'general')->where('key', 'hero_notice_enabled')->value('value'))
        ->toBe('1')
        ->and(Setting::query()->where('section', 'general')->where('key', 'hero_notice_text')->value('value'))
        ->toBe('Perpustakaan tutup sementara pada hari Sabtu.')
        ->and(Setting::query()->where('section', 'general')->where('key', 'hero_notice_url')->value('value'))
        ->toBe('https://example.com/pengumuman')
        ->and(Setting::query()->where('section', 'general')->where('key', 'hero_notice_link_label')->value('value'))
        ->toBe('Lihat info')
        ->and(Setting::query()->where('section', 'general')->where('key', 'hero_notice_tone')->value('value'))
        ->toBe('warning')
        ->and($kioskPinHash !== '')
        ->toBeTrue()
        ->and(Hash::check('123456', $kioskPinHash))
        ->toBeTrue();
});
