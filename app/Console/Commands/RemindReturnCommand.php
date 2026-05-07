<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Notifications\LoanReminderNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:remind-return')]
#[Description('Send email reminders for books due tomorrow')]
class RemindReturnCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = now()->addDay()->startOfDay();
        $tomorrowEnd = now()->addDay()->endOfDay();

        $loans = Loan::query()
            ->where('status', '=', Loan::STATUS_BORROWED)
            ->whereNull('returned_at', 'and', false)
            ->whereNull('reminder_sent_at', 'and', false)
            ->whereBetween('due_at', [$tomorrow, $tomorrowEnd], 'and')
            ->with('user', 'items.bookItem.book')
            ->get();

        if ($loans->isEmpty()) {
            $this->info('No books are due tomorrow.');

            return;
        }

        $this->info("Sending reminders for {$loans->count()} loans...");

        foreach ($loans as $loan) {
            $loan->user->notify(new LoanReminderNotification($loan));
            $loan->reminder_sent_at = now();
            $loan->save();
        }

        $this->info('Reminders sent successfully!');
    }
}
