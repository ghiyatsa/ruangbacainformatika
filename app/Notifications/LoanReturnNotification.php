<?php

namespace App\Notifications;

use App\Notifications\Channels\WhatsAppChannel;
use App\Notifications\Concerns\RateLimitsWhatsAppNotifications;
use App\Notifications\Messages\WhatsAppMessage;
use Illuminate\Notifications\Notification;
use Throwable;

class LoanReturnNotification extends Notification
{
    use RateLimitsWhatsAppNotifications;

    /**
     * @param  list<string>  $bookTitles
     */
    public function __construct(
        protected array $bookTitles,
        protected string $returnedAt,
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
            'Buku berikut sudah berhasil dikembalikan:',
        ];

        foreach ($this->bookTitles as $bookTitle) {
            $lines[] = "- {$bookTitle}";
        }

        $lines[] = '';
        $lines[] = 'Waktu pengembalian: '.$this->returnedAt;
        $lines[] = '';
        $lines[] = 'Terima kasih!';

        return new WhatsAppMessage(
            implode("\n", $lines),
            category: 'loan_return',
            templateName: 'loan_return',
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
            'returned_count' => count($this->bookTitles),
            'book_titles' => $this->bookTitles,
            'returned_at' => $this->returnedAt,
        ];
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception) {
            report($exception);
        }
    }
}
