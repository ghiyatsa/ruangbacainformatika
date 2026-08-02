<?php

namespace App\Console\Commands;

use App\Services\LoanReminderService;
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
    public function handle(LoanReminderService $reminderService): int
    {
        $maxOverdueDays = max((int) $this->option('max-overdue-days'), 0);

        $loans = $reminderService->eligibleLoansQuery($maxOverdueDays)
            ->with('user', 'items.bookItem.book')
            ->get();

        if ($loans->isEmpty()) {
            $this->info('Tidak ada pinjaman yang perlu diingatkan hari ini.');

            return self::SUCCESS;
        }

        $this->info("Mengirim reminder untuk {$loans->count()} pinjaman...");

        foreach ($loans as $loan) {
            $reminderService->remind($loan);
        }

        $this->info('Reminder berhasil dikirim.');

        return self::SUCCESS;
    }
}
