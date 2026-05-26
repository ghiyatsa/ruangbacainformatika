<?php

use App\Models\Loan;
use App\Models\User;
use App\Notifications\Channels\WhatsAppChannel;
use App\Notifications\LoanReceiptNotification;
use App\Notifications\LoanReminderNotification;
use App\Notifications\LoanReturnNotification;
use App\Notifications\WhatsAppOtpNotification;
use Illuminate\Queue\Middleware\RateLimited;

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
            ->and($middleware[0])->toBeInstanceOf(RateLimited::class);
    }
});

it('does not add whatsapp rate limiting middleware for non whatsapp channels', function () {
    $user = User::factory()->make();
    $loan = Loan::factory()->make();

    $middleware = (new LoanReceiptNotification($loan))->middleware($user, 'database');

    expect($middleware)->toBe([]);
});

it('marks whatsapp otp messages to bypass global pacing', function () {
    $user = User::factory()->make([
        'name' => 'OTP User',
    ]);

    $message = (new WhatsAppOtpNotification('123456'))->toWhatsApp($user);

    expect($message->bypassPacing)->toBeTrue()
        ->and($message->content)->toContain('123456');
});
