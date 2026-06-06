<?php

namespace App\Notifications;

use App\Notifications\Channels\WhatsAppChannel;
use App\Notifications\Messages\WhatsAppMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WhatsAppOtpNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected string $code,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        $lines = [
            "Halo {$notifiable->name},",
            'Kode OTP WhatsApp Ruang Baca Anda:',
            $this->code,
            '',
            'Kode berlaku selama 10 menit.',
            'Jangan bagikan kode ini kepada siapa pun.',
        ];

        return new WhatsAppMessage(
            implode("\n", $lines),
            bypassPacing: true,
            category: 'otp',
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'channel' => 'whatsapp',
        ];
    }
}
