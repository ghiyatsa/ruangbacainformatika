<?php

namespace App\Notifications;

use App\Models\Loan;
use App\Notifications\Channels\WhatsAppChannel;
use App\Notifications\Concerns\RateLimitsWhatsAppNotifications;
use App\Notifications\Messages\WhatsAppMessage;
use App\Support\AppTimezone;
use Illuminate\Notifications\Notification;
use Throwable;

class LoanReceiptNotification extends Notification
{
    use RateLimitsWhatsAppNotifications;

    public function __construct(
        protected Loan $loan
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
            'Peminjaman buku Anda berhasil:',
        ];

        foreach ($this->loan->items as $item) {
            $title = $item->bookItem->book->title ?? 'Buku Tanpa Judul';
            $lines[] = "- {$title}";
        }

        $lines[] = '';
        $lines[] = 'Batas waktu pengembalian: '.AppTimezone::format($this->loan->due_at, 'd F Y');
        $lines[] = '';
        $lines[] = 'Harap kembalikan tepat waktu ya. Terima kasih!';

        return new WhatsAppMessage(
            implode("\n", $lines),
            category: 'loan_receipt',
            templateName: 'loan_receipt',
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
            'loan_id' => $this->loan->id,
            'items_count' => $this->loan->items->count(),
            'due_at' => $this->loan->due_at,
        ];
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception) {
            report($exception);
        }
    }
}
