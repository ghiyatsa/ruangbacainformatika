<?php

namespace App\Notifications;

use App\Models\Loan;
use App\Support\AppTimezone;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LoanReceiptDatabaseNotification extends Notification
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

        $itemsCount = count($bookTitles);

        return [
            'kind' => 'loan_receipt',
            'title' => 'Peminjaman berhasil diproses',
            'message' => sprintf(
                '%d buku tercatat dalam peminjaman Anda. Batas pengembalian: %s.',
                $itemsCount,
                AppTimezone::format($this->loan->due_at, 'd F Y'),
            ),
            'action_label' => 'Lihat riwayat',
            'action_url' => route('loans.history', absolute: false),
            'icon' => 'book-check',
            'loan_id' => $this->loan->id,
            'items_count' => $itemsCount,
            'book_titles' => $bookTitles,
            'due_at' => $this->loan->due_at?->toIso8601String(),
        ];
    }
}
