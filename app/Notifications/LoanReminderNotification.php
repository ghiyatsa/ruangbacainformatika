<?php

namespace App\Notifications;

use App\Models\Loan;
use App\Notifications\Channels\WhatsAppChannel;
use App\Notifications\Concerns\RateLimitsWhatsAppNotifications;
use App\Notifications\Messages\WhatsAppMessage;
use App\Support\AppTimezone;
use Illuminate\Notifications\Notification;
use Throwable;

class LoanReminderNotification extends Notification
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
        $stage = $this->loan->reminderStage();
        $lateDays = $this->loan->lateDays();

        $greeting = "Halo {$notifiable->name},";
        $header = match ($stage) {
            Loan::REMINDER_STAGE_DUE_TODAY => 'Mengingatkan peminjaman buku Anda jatuh tempo hari ini.',
            Loan::REMINDER_STAGE_OVERDUE => "Peminjaman buku Anda telah melewati batas waktu ({$lateDays} hari).",
            default => 'Mengingatkan peminjaman buku Anda akan jatuh tempo besok.',
        };

        $lines = [
            $greeting,
            '',
            $header,
            'Daftar buku:',
        ];

        foreach ($this->loan->items as $item) {
            $title = $item->bookItem->book->title ?? 'Buku Tanpa Judul';
            $lines[] = "- {$title}";
        }

        $lines[] = '';
        $lines[] = 'Batas pengembalian: '.AppTimezone::format($this->loan->due_at, 'd F Y');
        $lines[] = '';
        $lines[] = 'Silakan lakukan pengembalian buku di Ruang Baca Informatika. Terima kasih!';

        return new WhatsAppMessage(
            implode("\n", $lines),
            category: 'loan_reminder',
            templateName: 'loan_due_reminder',
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
