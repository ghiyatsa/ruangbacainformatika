<?php

namespace App\Notifications;

use App\Models\Loan;
use App\Notifications\Channels\WhatsAppChannel;
use App\Notifications\Concerns\RateLimitsWhatsAppNotifications;
use App\Notifications\Messages\WhatsAppMessage;
use App\Support\AppTimezone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Throwable;

class LoanReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use RateLimitsWhatsAppNotifications;

    public int $tries = 12;

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
        $stage = $this->loan->reminderStage();
        $lateDays = $this->loan->lateDays();

        $lines = [
            "Assalamualaikum {$notifiable->name},",
            match ($stage) {
                Loan::REMINDER_STAGE_DUE_TODAY => 'Peminjaman buku Anda berakhir hari ini.',
                Loan::REMINDER_STAGE_OVERDUE => "Peminjaman Anda sudah melewati jatuh tempo (telat {$lateDays} hari).",
                default => 'Peminjaman buku Anda berakhir besok.',
            },
            'Buku yang perlu dikembalikan:',
        ];

        foreach ($this->loan->items as $item) {
            $title = $item->bookItem->book->title ?? 'Buku Tanpa Judul';
            $lines[] = "- {$title}";
        }

        $lines[] = '';
        $lines[] = match ($stage) {
            Loan::REMINDER_STAGE_OVERDUE => 'Batas pengembalian: '.AppTimezone::format($this->loan->due_at, 'd F Y')." (sudah terlambat {$lateDays} hari)",
            Loan::REMINDER_STAGE_DUE_TODAY => 'Batas pengembalian hari ini: '.AppTimezone::format($this->loan->due_at, 'd F Y'),
            default => 'Batas pengembalian: '.AppTimezone::format($this->loan->due_at, 'd F Y'),
        };
        $lines[] = 'Terima kasih! '.config('app.name');

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
