<?php

namespace App\Notifications;

use App\Models\Loan;
use App\Notifications\Channels\WhatsAppChannel;
use App\Notifications\Concerns\RateLimitsWhatsAppNotifications;
use App\Notifications\Messages\WhatsAppMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Throwable;

class LoanReceiptNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use RateLimitsWhatsAppNotifications;

    public int $tries = 12;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Loan $loan
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
            "Halo {$notifiable->name},",
            'Peminjaman buku Anda berhasil diproses melalui kiosk.',
            'Daftar buku:',
        ];

        foreach ($this->loan->items as $item) {
            $title = $item->bookItem->book->title ?? 'Buku Tanpa Judul';
            $isbn = $item->bookItem->book->isbn ?? '-';
            $lines[] = "- {$title} (ISBN: {$isbn})";
        }

        $lines[] = '';
        $lines[] = 'Batas pengembalian: '.$this->loan->due_at?->translatedFormat('d F Y');
        $lines[] = 'Silakan kembalikan tepat waktu untuk menghindari pembatasan akun.';
        $lines[] = 'Riwayat pinjaman: '.url('/loans/history');
        $lines[] = 'Salam, Tim Perpustakaan '.config('app.name');

        return new WhatsAppMessage(implode("\n", $lines));
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
