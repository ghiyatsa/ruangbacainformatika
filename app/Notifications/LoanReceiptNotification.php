<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Throwable;

class LoanReceiptNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 12;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Loan $loan
    ) {
        $this->afterCommit();
        $this->onQueue('mail');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Bukti Peminjaman Buku - '.config('app.name'))
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Peminjaman buku Anda telah berhasil diproses melalui perangkat kiosk.')
            ->line('Berikut adalah daftar buku yang Anda pinjam:')
            ->line('');

        foreach ($this->loan->items as $item) {
            $title = $item->bookItem->book->title ?? 'Buku Tanpa Judul';
            $isbn = $item->bookItem->book->isbn ?? '-';
            $mail->line("- **{$title}** (ISBN: {$isbn})");
        }

        return $mail
            ->line('')
            ->line('**Batas Pengembalian:** '.$this->loan->due_at?->translatedFormat('d F Y'))
            ->line('Pastikan untuk mengembalikan buku tepat waktu untuk menghindari denda atau penangguhan akun.')
            ->action('Lihat Riwayat Peminjaman', url('/dashboard'))
            ->line('Jika Anda merasa tidak melakukan peminjaman ini, silakan segera hubungi petugas perpustakaan untuk mengamankan akun Anda.')
            ->line('Terima kasih telah menggunakan layanan Perpustakaan '.config('app.name').'!')
            ->salutation('Salam, Tim Perpustakaan');
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
