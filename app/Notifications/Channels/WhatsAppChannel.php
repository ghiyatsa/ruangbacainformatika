<?php

namespace App\Notifications\Channels;

use App\Notifications\Messages\WhatsAppMessage;
use App\Services\WhatsAppGateway;
use Illuminate\Notifications\Notification;

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

        $this->gateway->send($phoneNumber, $message->content);
    }
}
