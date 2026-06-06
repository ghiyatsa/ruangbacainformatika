<?php

namespace App\Notifications\Channels;

use App\Models\WhatsAppMessageLog;
use App\Notifications\Messages\WhatsAppMessage;
use App\Services\WhatsAppGateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Throwable;

class WhatsAppChannel
{
    public function __construct(
        protected WhatsAppGateway $gateway,
    ) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $phoneNumber = method_exists($notifiable, 'routeNotificationForWhatsApp')
            ? $notifiable->routeNotificationForWhatsApp()
            : null;

        if (! is_string($phoneNumber) || $phoneNumber === '') {
            return;
        }

        $message = $notification->toWhatsApp($notifiable);

        if (! $message instanceof WhatsAppMessage) {
            return;
        }

        $log = $this->createLog($notifiable, $notification, $phoneNumber, $message);

        try {
            $this->gateway->sendMessage($phoneNumber, $message, $log);
        } catch (Throwable $exception) {
            if ($message->category === 'otp' || $message->bypassPacing || $notification instanceof ShouldQueue) {
                throw $exception;
            }

            report($exception);
        }
    }

    protected function createLog(
        object $notifiable,
        Notification $notification,
        string $phoneNumber,
        WhatsAppMessage $message,
    ): WhatsAppMessageLog {
        return WhatsAppMessageLog::query()->create([
            'user_id' => method_exists($notifiable, 'getKey') && is_numeric($notifiable->getKey())
                ? (int) $notifiable->getKey()
                : null,
            'category' => $message->category,
            'notification_type' => $notification::class,
            'phone_number_hash' => hash('sha256', $phoneNumber),
            'phone_number_masked' => $this->maskPhoneNumber($phoneNumber),
            'message_preview' => Str::limit($message->content, 500, ''),
        ]);
    }

    protected function maskPhoneNumber(string $phoneNumber): string
    {
        $digits = preg_replace('/\D+/', '', $phoneNumber) ?? '';

        if (strlen($digits) <= 6) {
            return $digits;
        }

        return Str::substr($digits, 0, 4)
            .str_repeat('*', max(0, strlen($digits) - 6))
            .Str::substr($digits, -2);
    }
}
