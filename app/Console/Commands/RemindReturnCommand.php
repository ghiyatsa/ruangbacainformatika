<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Notifications\LoanReminderDatabaseNotification;
use App\Notifications\LoanReminderNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:remind-return {--max-overdue-days=7 : Berhenti kirim reminder setelah sekian hari telat}')]
#[Description('Send WhatsApp reminders from H-1 until books are returned or overdue cap is reached')]
class RemindReturnCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $maxOverdueDays = max((int) $this->option('max-overdue-days'), 0);

        $loans = Loan::query()
            ->where('status', Loan::STATUS_BORROWED)
            ->whereNull('returned_at')
            ->where('due_at', '<=', now()->addDay()->endOfDay())
            ->where('due_at', '>=', now()->subDays($maxOverdueDays)->startOfDay())
            ->where(function ($query): void {
                $query
                    ->whereNull('reminder_sent_at')
                    ->orWhere('reminder_sent_at', '<', now()->startOfDay());
            })
            ->with('user', 'items.bookItem.book')
            ->get();

        if ($loans->isEmpty()) {
            $this->info('Tidak ada pinjaman yang perlu diingatkan hari ini.');

            return self::SUCCESS;
        }

        $this->info("Mengirim reminder untuk {$loans->count()} pinjaman...");

        foreach ($loans as $loan) {
            $loan->user->notify(new LoanReminderDatabaseNotification($loan));
            $loan->user->notify(new LoanReminderNotification($loan));
            $loan->reminder_sent_at = now();
            $loan->save();
        }

        $this->info('Reminder berhasil dikirim.');

        return self::SUCCESS;
    }
}
