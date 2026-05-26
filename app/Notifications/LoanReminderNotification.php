<?php

namespace App\Notifications;

use App\Models\Loan;
use App\Notifications\Channels\WhatsAppChannel;
use App\Notifications\Concerns\RateLimitsWhatsAppNotifications;
use App\Notifications\Messages\WhatsAppMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LoanReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use RateLimitsWhatsAppNotifications;

    /**
     * Create a new notification instance.
     */
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
            'Ini pengingat bahwa masa peminjaman buku Anda berakhir besok.',
            'Buku yang perlu dikembalikan:',
        ];

        foreach ($this->loan->items as $item) {
            $title = $item->bookItem->book->title ?? 'Buku Tanpa Judul';
            $lines[] = "- {$title}";
        }

        $lines[] = '';
        $lines[] = 'Batas pengembalian: '.$this->loan->due_at?->translatedFormat('d F Y');
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
            'due_at' => $this->loan->due_at,
        ];
    }
}
