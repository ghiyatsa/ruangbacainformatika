<?php

namespace App\Notifications;

use App\Notifications\Channels\WhatsAppChannel;
use App\Notifications\Messages\WhatsAppMessage;
use Illuminate\Notifications\Notification;

class WhatsAppOtpNotification extends Notification
{
    public function __construct(
        protected string $code,
    ) {}

    /**
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
            '',
            'Kode verifikasi akun Ruang Baca Anda:',
            "*{$this->code}*",
            '',
            'Kode berlaku selama 10 menit. Jangan berikan kode ini kepada siapa pun.',
        ];

        return new WhatsAppMessage(
            implode("\n", $lines),
            category: 'otp',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'channel' => 'whatsapp',
        ];
    }
}
