<?php

namespace App\Notifications;

use App\Notifications\Channels\WhatsAppChannel;
use App\Notifications\Concerns\RateLimitsWhatsAppNotifications;
use App\Notifications\Messages\WhatsAppMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Throwable;

class LoanReturnNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use RateLimitsWhatsAppNotifications;

    public int $tries = 12;

    /**
     * @param  list<string>  $bookTitles
     */
    public function __construct(
        protected array $bookTitles,
        protected string $returnedAt,
    ) {
        $this->afterCommit();
    }

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
            "Assalamualaikum {$notifiable->name},",
            'Pengembalian buku Anda berhasil diproses.',
            'Buku yang telah dikembalikan:',
        ];

        foreach ($this->bookTitles as $bookTitle) {
            $lines[] = "- {$bookTitle}";
        }

        $lines[] = '';
        $lines[] = 'Waktu pengembalian: '.$this->returnedAt;
        $lines[] = 'Terima kasih! '.config('app.name');

        return new WhatsAppMessage(
            implode("\n", $lines),
            category: 'loan_return',
            templateName: 'loan_return',
        );
    }

    /**
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

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [300, 900, 1800, 3600];
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addDay();
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception) {
            report($exception);
        }
    }
}
