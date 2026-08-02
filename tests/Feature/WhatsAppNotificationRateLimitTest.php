<?php

use App\Models\Loan;
use App\Models\User;
use App\Models\WhatsAppMessageLog;
use App\Notifications\Channels\WhatsAppChannel;
use App\Notifications\LoanReceiptNotification;
use App\Notifications\LoanReminderNotification;
use App\Notifications\LoanReturnNotification;
use App\Notifications\Messages\WhatsAppMessage;
use App\Notifications\WhatsAppOtpNotification;
use App\Repositories\SettingRepository;
use App\Services\WhatsAppGateway;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\assertDatabaseHas;

it('applies rate limiting middleware to queued whatsapp notifications', function () {
    config()->set('services.fonnte.send_interval_seconds', 15);

    $user = User::factory()->make();
    $loan = Loan::factory()->make();

    $notifications = [
        new LoanReceiptNotification($loan),
        new LoanReminderNotification($loan),
        new LoanReturnNotification(['Buku Laravel'], now()->translatedFormat('d F Y H:i')),
    ];

    foreach ($notifications as $notification) {
        $middleware = $notification->middleware($user, WhatsAppChannel::class);

        expect($middleware)->toHaveCount(1)
            ->and($middleware[0])->toBeInstanceOf(RateLimited::class)
            ->and($notification->viaQueues())->toBe([
                WhatsAppChannel::class => 'whatsapp',
            ]);
    }
});

it('does not add whatsapp rate limiting middleware for non whatsapp channels', function () {
    $user = User::factory()->make();
    $loan = Loan::factory()->make();

    $middleware = (new LoanReceiptNotification($loan))->middleware($user, 'database');

    expect($middleware)->toBe([]);
});

it('marks whatsapp otp messages for dedicated pacing instead of global pacing', function () {
    $user = User::factory()->make([
        'name' => 'OTP User',
    ]);

    $message = (new WhatsAppOtpNotification('123456'))->toWhatsApp($user);

    expect($message->bypassPacing)->toBeFalse()
        ->and($message->category)->toBe('otp')
        ->and($message->content)->toContain('123456');
});

it('logs successful whatsapp deliveries through the channel', function () {
    config()->set('services.fonnte.url', 'https://api.fonnte.com/send');
    config()->set('services.fonnte.token', 'plain-token-value');

    Http::fake([
        'https://api.fonnte.com/send' => Http::response([
            'status' => true,
            'detail' => 'success! message in queue',
            'id' => 'wa-message-1',
        ], 200),
    ]);

    $user = User::factory()->create([
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
    ]);

    app(WhatsAppChannel::class)->send(
        $user,
        new LoanReturnNotification(['Buku Laravel'], now()->translatedFormat('d F Y H:i')),
    );

    assertDatabaseHas('whats_app_message_logs', [
        'user_id' => $user->id,
        'category' => 'loan_return',
        'status' => WhatsAppMessageLog::StatusSent,
        'provider_message_id' => 'wa-message-1',
    ]);
});

it('marks disconnected fonnte devices as failed and lets queued loan notifications retry later', function () {
    config()->set('services.fonnte.url', 'https://api.fonnte.com/send');
    config()->set('services.fonnte.token', 'plain-token-value');

    Http::fake([
        'https://api.fonnte.com/send' => Http::response([
            'status' => false,
            'detail' => 'device disconnected',
        ], 200),
    ]);

    $user = User::factory()->create([
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
    ]);

    expect(fn () => app(WhatsAppChannel::class)->send(
        $user,
        new LoanReturnNotification(['Buku Laravel'], now()->translatedFormat('d F Y H:i')),
    ))->toThrow(RuntimeException::class, 'device disconnected');

    Http::assertSent(fn ($request): bool => $request['connectOnly'] === true);

    assertDatabaseHas('whats_app_message_logs', [
        'user_id' => $user->id,
        'category' => 'loan_return',
        'status' => WhatsAppMessageLog::StatusFailed,
        'provider_status' => 'rejected',
        'error_message' => 'device disconnected',
    ]);
});

it('pauses all whatsapp delivery after recent failures including otp', function () {
    config()->set('services.fonnte.url', 'https://api.fonnte.com/send');
    config()->set('services.fonnte.token', 'plain-token-value');
    config()->set('services.fonnte.failure_pause_threshold', 1);
    config()->set('services.fonnte.failure_pause_window_minutes', 15);

    Http::fake();

    WhatsAppMessageLog::query()->create([
        'category' => 'loan_return',
        'status' => WhatsAppMessageLog::StatusFailed,
        'phone_number_hash' => hash('sha256', '08123456789'),
        'phone_number_masked' => '0812*****89',
        'error_message' => 'gateway error',
        'failed_at' => now(),
    ]);

    $gateway = new WhatsAppGateway(app(SettingRepository::class), app(HttpFactory::class));

    expect(fn () => $gateway->sendMessage(
        '08123456789',
        new WhatsAppMessage('Pengingat rutin', category: 'loan_reminder'),
    ))->toThrow(RuntimeException::class, 'dijeda sementara');

    expect(fn () => $gateway->sendMessage(
        '08123456789',
        new WhatsAppMessage('Kode OTP: 123456', category: 'otp'),
    ))->toThrow(RuntimeException::class, 'dijeda sementara');

    Http::assertSentCount(0);
});

it('skips routine whatsapp delivery when the daily cap is reached', function () {
    config()->set('services.fonnte.url', 'https://api.fonnte.com/send');
    config()->set('services.fonnte.token', 'plain-token-value');
    config()->set('services.fonnte.daily_limit', 1);

    Http::fake();

    $user = User::factory()->create([
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
    ]);

    WhatsAppMessageLog::query()->create([
        'user_id' => $user->id,
        'category' => 'loan_return',
        'status' => WhatsAppMessageLog::StatusSent,
        'phone_number_hash' => hash('sha256', '08123456789'),
        'phone_number_masked' => '0812*****89',
        'sent_at' => now(),
    ]);

    app(WhatsAppChannel::class)->send(
        $user,
        new LoanReturnNotification(['Buku Laravel'], now()->translatedFormat('d F Y H:i')),
    );

    Http::assertSentCount(0);

    assertDatabaseHas('whats_app_message_logs', [
        'user_id' => $user->id,
        'category' => 'loan_return',
        'status' => WhatsAppMessageLog::StatusSkipped,
        'error_message' => 'Melebihi batas harian pengiriman WhatsApp untuk nomor ini.',
    ]);
});

it('sends routine whatsapp delivery when below the daily cap', function () {
    config()->set('services.fonnte.url', 'https://api.fonnte.com/send');
    config()->set('services.fonnte.token', 'plain-token-value');
    config()->set('services.fonnte.daily_limit', 10);

    Http::fake([
        'https://api.fonnte.com/send' => Http::response([
            'status' => true,
            'id' => 'wa-1',
        ], 200),
    ]);

    $user = User::factory()->create([
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
    ]);

    app(WhatsAppChannel::class)->send(
        $user,
        new LoanReturnNotification(['Buku Laravel'], now()->translatedFormat('d F Y H:i')),
    );

    Http::assertSentCount(1);

    assertDatabaseHas('whats_app_message_logs', [
        'user_id' => $user->id,
        'category' => 'loan_return',
        'status' => WhatsAppMessageLog::StatusSent,
    ]);
});
