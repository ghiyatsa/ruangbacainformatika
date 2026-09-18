<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\LoanReminderService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:remind-return')]
#[Description('Send WhatsApp reminders from H-1 onwards until the books are returned')]
class RemindReturnCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(LoanReminderService $reminderService): int
    {
        $loans = $reminderService->eligibleLoansQuery()
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
