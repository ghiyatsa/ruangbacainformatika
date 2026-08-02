<?php

namespace App\Notifications;

use App\Models\Loan;
use App\Support\AppTimezone;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LoanReminderDatabaseNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Loan $loan,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->loan->loadMissing('items.bookItem.book');

        $stage = $this->loan->reminderStage();
        $lateDays = $this->loan->lateDays();

        [$title, $message] = match ($stage) {
            Loan::REMINDER_STAGE_DUE_TODAY => [
                'Batas pengembalian hari ini',
                sprintf('Buku harus dikembalikan hari ini. Batas akhir %s.', AppTimezone::format($this->loan->due_at, 'd F Y')),
            ],
            Loan::REMINDER_STAGE_OVERDUE => [
                "Pengembalian sudah telat {$lateDays} hari",
                sprintf('Buku belum dikembalikan hingga %s. Segera kembalikan untuk menghindari konsekuensi.', AppTimezone::format($this->loan->due_at, 'd F Y')),
            ],
            default => [
                'Batas pengembalian hampir tiba',
                sprintf('Pinjaman Anda jatuh tempo pada %s. Pastikan buku dikembalikan tepat waktu.', AppTimezone::format($this->loan->due_at, 'd F Y')),
            ],
        };

        $bookTitles = $this->loan->items
            ->map(fn ($item): string => $item->bookItem->book->title ?? 'Buku Tanpa Judul')
            ->values()
            ->all();

        return [
            'kind' => 'loan_reminder',
            'title' => $title,
            'message' => $message,
            'action_label' => 'Buka riwayat',
            'action_url' => route('loans.history', absolute: false),
            'icon' => 'bell-ring',
            'loan_id' => $this->loan->id,
            'book_titles' => $bookTitles,
            'due_at' => $this->loan->due_at?->toIso8601String(),
        ];
    }
}
