<?php

use App\Repositories\SettingRepository;
use App\Services\WhatsAppGateway;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('sends whatsapp messages to fonnte with the expected authorization header', function () {
    Http::fake([
        'https://api.fonnte.com/send' => Http::response([
            'status' => true,
            'detail' => 'success! message in queue',
        ], 200),
    ]);

    $repository = mock(SettingRepository::class);
    $repository->shouldReceive('get')->with('integration', 'whatsapp_api_url')->andReturn('https://api.fonnte.com/send');
    $repository->shouldReceive('get')->with('integration', 'whatsapp_api_token')->andReturn('plain-token-value');

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
            && $request['message'] === 'Kode OTP Anda: 123456';
    });
});

it('throws a runtime exception when fonnte rejects the message request', function () {
    Http::fake([
        'https://api.fonnte.com/send' => Http::response([
            'status' => false,
            'reason' => 'token invalid',
        ], 200),
    ]);

    $repository = mock(SettingRepository::class);
    $repository->shouldReceive('get')->with('integration', 'whatsapp_api_url')->andReturn('https://api.fonnte.com/send');
    $repository->shouldReceive('get')->with('integration', 'whatsapp_api_token')->andReturn('plain-token-value');

    expect(fn () => (new WhatsAppGateway($repository, app(HttpFactory::class)))->send(
        '08123456789',
        'Kode OTP Anda: 123456',
    ))->toThrow(RuntimeException::class, 'token invalid');
});
