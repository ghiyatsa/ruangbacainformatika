<?php

use App\Repositories\SettingRepository;
use App\Services\WhatsAppGateway;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('sends whatsapp messages to fonnte with the expected authorization header', function () {
    config()->set('services.fonnte.url', 'https://api.fonnte.com/send');
    config()->set('services.fonnte.token', 'plain-token-value');

    Http::fake([
        'https://api.fonnte.com/send' => Http::response([
            'status' => true,
            'detail' => 'success! message in queue',
        ], 200),
    ]);

    $repository = mock(SettingRepository::class);
    $repository->shouldReceive('get')->with('integration', 'whatsapp_api_url', 'https://api.fonnte.com/send')->andReturn('https://api.fonnte.com/send');
    $repository->shouldReceive('get')->with('integration', 'whatsapp_api_token', 'plain-token-value')->andReturn('plain-token-value');
    allowWhatsAppHealthSettings($repository);

    $response = (new WhatsAppGateway($repository, app(HttpFactory::class)))->send(
        '08123456789',
        'Kode OTP Anda: 123456',
    );

    expect($response->successful())->toBeTrue();

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://api.fonnte.com/send'
            && $request->hasHeader('Authorization', 'plain-token-value')
            && ! $request->hasHeader('Authorization', 'Bearer plain-token-value')
            && $request['target'] === '08123456789'
            && $request['message'] === 'Kode OTP Anda: 123456'
            && $request['connectOnly'] === true;
    });
});

it('throws a runtime exception when fonnte rejects the message request', function () {
    config()->set('services.fonnte.url', 'https://api.fonnte.com/send');
    config()->set('services.fonnte.token', 'plain-token-value');

    Http::fake([
        'https://api.fonnte.com/send' => Http::response([
            'status' => false,
            'reason' => 'token invalid',
        ], 200),
    ]);

    $repository = mock(SettingRepository::class);
    $repository->shouldReceive('get')->with('integration', 'whatsapp_api_url', 'https://api.fonnte.com/send')->andReturn('https://api.fonnte.com/send');
    $repository->shouldReceive('get')->with('integration', 'whatsapp_api_token', 'plain-token-value')->andReturn('plain-token-value');
    allowWhatsAppHealthSettings($repository);

    expect(fn () => (new WhatsAppGateway($repository, app(HttpFactory::class)))->send(
        '08123456789',
        'Kode OTP Anda: 123456',
    ))->toThrow(RuntimeException::class, 'token invalid');
});

it('falls back to env-backed config when whatsapp integration settings are missing', function () {
    config()->set('services.fonnte.url', 'https://api.fonnte.com/send');
    config()->set('services.fonnte.token', 'env-token-value');

    Http::fake([
        'https://api.fonnte.com/send' => Http::response([
            'status' => true,
            'detail' => 'success! message in queue',
        ], 200),
    ]);

    $repository = mock(SettingRepository::class);
    $repository->shouldReceive('get')
        ->with('integration', 'whatsapp_api_url', 'https://api.fonnte.com/send')
        ->andReturn('https://api.fonnte.com/send');
    $repository->shouldReceive('get')
        ->with('integration', 'whatsapp_api_token', 'env-token-value')
        ->andReturn('env-token-value');
    allowWhatsAppHealthSettings($repository);

    $response = (new WhatsAppGateway($repository, app(HttpFactory::class)))->send(
        '08123456789',
        'Pesan dari env',
    );

    expect($response->successful())->toBeTrue();
});

it('decrypts encrypted whatsapp integration tokens before sending messages', function () {
    config()->set('services.fonnte.url', 'https://api.fonnte.com/send');
    config()->set('services.fonnte.token', 'env-token-value');

    Http::fake([
        'https://api.fonnte.com/send' => Http::response([
            'status' => true,
            'detail' => 'success! message in queue',
        ], 200),
    ]);

    $encryptedToken = encrypt('stored-encrypted-token');

    $repository = mock(SettingRepository::class);
    $repository->shouldReceive('get')
        ->with('integration', 'whatsapp_api_url', 'https://api.fonnte.com/send')
        ->andReturn('https://api.fonnte.com/send');
    $repository->shouldReceive('get')
        ->with('integration', 'whatsapp_api_token', 'env-token-value')
        ->andReturn($encryptedToken);
    allowWhatsAppHealthSettings($repository);

    $response = (new WhatsAppGateway($repository, app(HttpFactory::class)))->send(
        '08123456789',
        'Pesan terenkripsi',
    );

    expect($response->successful())->toBeTrue();

    Http::assertSent(function (Request $request): bool {
        return $request->hasHeader('Authorization', 'stored-encrypted-token');
    });
});

function allowWhatsAppHealthSettings(object $repository): void
{
    $repository->shouldReceive('get')
        ->with('integration', 'whatsapp_failure_pause_threshold', 5)
        ->andReturn(5);
    $repository->shouldReceive('get')
        ->with('integration', 'whatsapp_failure_pause_window_minutes', 15)
        ->andReturn(15);
}
