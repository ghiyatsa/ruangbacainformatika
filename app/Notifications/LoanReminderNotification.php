<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Loan $loan
    ) {}

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
            ->subject('Pengingat: Pengembalian Buku Besok - '.config('app.name'))
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Ini adalah pengingat bahwa masa peminjaman buku Anda akan berakhir besok.')
            ->line('Berikut adalah daftar buku yang perlu dikembalikan:')
            ->line('');

        foreach ($this->loan->items as $item) {
            $title = $item->bookItem->book->title ?? 'Buku Tanpa Judul';
            $mail->line("- **{$title}**");
        }

        return $mail
            ->line('')
            ->line('**Batas Pengembalian:** '.$this->loan->due_at?->translatedFormat('d F Y'))
            ->line('Mohon segera kembalikan buku tersebut tepat waktu untuk menghindari denda.')
            ->action('Lihat Riwayat Peminjaman', url('/dashboard'))
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
            'due_at' => $this->loan->due_at,
        ];
    }
}
