<?php

use App\Filament\Widgets\WhatsAppGatewayStatusWidget;
use App\Models\WhatsAppMessageLog;
use App\Repositories\SettingRepository;
use App\Services\WhatsAppGateway;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

use function Livewire\invade;

it('derives the fonnte device endpoint from the send endpoint', function () {
    config()->set('services.fonnte.url', 'https://api.fonnte.com/send');
    config()->set('services.fonnte.token', 'plain-token-value');

    $repository = mock(SettingRepository::class);
    $repository->shouldReceive('get')->with('integration', 'whatsapp_api_url', 'https://api.fonnte.com/send')->andReturn('https://api.fonnte.com/send');
    $repository->shouldReceive('get')->with('integration', 'whatsapp_api_token', 'plain-token-value')->andReturn('plain-token-value');

    $gateway = new WhatsAppGateway($repository, app(HttpFactory::class));

    expect($gateway->deviceUrl())->toBe('https://api.fonnte.com/device');
});

it('reports the gateway as connected when fonnte returns a connect device status', function () {
    config()->set('services.fonnte.url', 'https://api.fonnte.com/send');
    config()->set('services.fonnte.token', 'plain-token-value');

    Http::fake([
        'https://api.fonnte.com/device' => Http::response([
            'device' => '6282227097005',
            'device_status' => 'connect',
            'name' => 'Fonnte admin',
            'status' => true,
        ], 200),
    ]);

    $repository = mock(SettingRepository::class);
    $repository->shouldReceive('get')->with('integration', 'whatsapp_api_url', 'https://api.fonnte.com/send')->andReturn('https://api.fonnte.com/send');
    $repository->shouldReceive('get')->with('integration', 'whatsapp_api_token', 'plain-token-value')->andReturn('plain-token-value');

    $status = (new WhatsAppGateway($repository, app(HttpFactory::class)))->deviceStatus();

    expect($status['configured'])->toBeTrue()
        ->and($status['connected'])->toBeTrue()
        ->and($status['device'])->toBe('6282227097005');
});

it('reports the gateway as disconnected when fonnte rejects the token', function () {
    config()->set('services.fonnte.url', 'https://api.fonnte.com/send');
    config()->set('services.fonnte.token', 'plain-token-value');

    Http::fake([
        'https://api.fonnte.com/device' => Http::response([
            'reason' => 'token invalid',
            'status' => false,
        ], 200),
    ]);

    $repository = mock(SettingRepository::class);
    $repository->shouldReceive('get')->with('integration', 'whatsapp_api_url', 'https://api.fonnte.com/send')->andReturn('https://api.fonnte.com/send');
    $repository->shouldReceive('get')->with('integration', 'whatsapp_api_token', 'plain-token-value')->andReturn('plain-token-value');

    $status = (new WhatsAppGateway($repository, app(HttpFactory::class)))->deviceStatus();

    expect($status['configured'])->toBeTrue()
        ->and($status['connected'])->toBeFalse()
        ->and($status['reason'])->toBe('token invalid');
});

it('renders a disconnected whatsapp gateway stat in the dashboard widget', function () {
    config()->set('services.fonnte.url', null);
    config()->set('services.fonnte.token', null);

    $repository = mock(SettingRepository::class);
    $repository->shouldReceive('get')->with('integration', 'whatsapp_api_url', null)->andReturn(null);
    $repository->shouldReceive('get')->with('integration', 'whatsapp_api_token', null)->andReturn(null);

    app()->instance(WhatsAppGateway::class, new WhatsAppGateway($repository, app(HttpFactory::class)));

    $stats = invade(app(WhatsAppGatewayStatusWidget::class))->getStats();

    expect($stats[0]->getLabel())->toBe('WhatsApp Gateway')
        ->and($stats[0]->getValue())->toBe('Belum Dikonfigurasi');
});

it('counts today sent and failed whatsapp messages in the dashboard widget', function () {
    config()->set('services.fonnte.url', null);
    config()->set('services.fonnte.token', null);

    WhatsAppMessageLog::query()->create([
        'category' => 'otp',
        'notification_type' => 'test',
        'status' => WhatsAppMessageLog::StatusSent,
        'attempts' => 1,
    ]);

    WhatsAppMessageLog::query()->create([
        'category' => 'otp',
        'notification_type' => 'test',
        'status' => WhatsAppMessageLog::StatusFailed,
        'attempts' => 1,
        'error_message' => 'token invalid',
    ]);

    $staleLog = WhatsAppMessageLog::query()->create([
        'category' => 'otp',
        'notification_type' => 'old',
        'status' => WhatsAppMessageLog::StatusSent,
        'attempts' => 1,
    ]);

    DB::table('whats_app_message_logs')
        ->where('id', $staleLog->id)
        ->update(['created_at' => now()->subDays(3)]);

    $repository = mock(SettingRepository::class);
    $repository->shouldReceive('get')->with('integration', 'whatsapp_api_url', null)->andReturn(null);
    $repository->shouldReceive('get')->with('integration', 'whatsapp_api_token', null)->andReturn(null);

    app()->instance(WhatsAppGateway::class, new WhatsAppGateway($repository, app(HttpFactory::class)));

    $stats = invade(app(WhatsAppGatewayStatusWidget::class))->getStats();

    expect($stats[1]->getLabel())->toBe('Pesan Terkirim Hari Ini')
        ->and($stats[1]->getValue())->toBe('1')
        ->and($stats[2]->getLabel())->toBe('Pesan Gagal Hari Ini')
        ->and($stats[2]->getValue())->toBe('1');
});
