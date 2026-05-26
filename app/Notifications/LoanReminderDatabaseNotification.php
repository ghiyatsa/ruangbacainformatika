<?php

namespace App\Notifications;

use App\Models\Loan;
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

        $bookTitles = $this->loan->items
            ->map(fn ($item): string => $item->bookItem->book->title ?? 'Buku Tanpa Judul')
            ->values()
            ->all();

        return [
            'kind' => 'loan_reminder',
            'title' => 'Batas pengembalian hampir tiba',
            'message' => sprintf(
                'Pinjaman Anda jatuh tempo pada %s. Pastikan buku dikembalikan tepat waktu.',
                $this->loan->due_at?->translatedFormat('d F Y') ?? '-',
            ),
            'action_label' => 'Buka riwayat',
            'action_url' => route('loans.history', absolute: false),
            'icon' => 'bell-ring',
            'loan_id' => $this->loan->id,
            'book_titles' => $bookTitles,
            'due_at' => $this->loan->due_at?->toIso8601String(),
        ];
    }
}
