<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\User;
use App\Notifications\LoanReminderDatabaseNotification;
use App\Notifications\LoanReminderNotification;
use Illuminate\Database\Eloquent\Builder;

class LoanReminderService
{
    protected const DEFAULT_MAX_OVERDUE_DAYS = 7;

    /**
     * Kirim reminder pengembalian untuk satu pinjaman (jika memenuhi syarat).
     */
    public function remind(Loan $loan): bool
    {
        if (! $this->isEligible($loan)) {
            return false;
        }

        $loan->loadMissing(['user', 'items.bookItem.book']);

        $loan->user->notify(new LoanReminderDatabaseNotification($loan));
        $loan->user->notify(new LoanReminderNotification($loan));
        $loan->reminder_sent_at = now();
        $loan->save();

        return true;
    }

    /**
     * Kirim reminder untuk semua pinjaman aktif yang memenuhi syarat milik member.
     */
    public function remindAllActive(User $user): int
    {
        $sent = 0;

        $this->eligibleLoansQuery()
            ->whereBelongsTo($user)
            ->get()
            ->each(function (Loan $loan) use (&$sent): void {
                if ($this->remind($loan)) {
                    $sent++;
                }
            });

        return $sent;
    }

    /**
     * Query pinjaman yang layak diingatkan: H-1 s.d. telat (cap), belum diingatkan hari ini.
     */
    public function eligibleLoansQuery(int $maxOverdueDays = self::DEFAULT_MAX_OVERDUE_DAYS): Builder
    {
        return Loan::query()
            ->where('status', Loan::STATUS_BORROWED)
            ->whereNull('returned_at')
            ->where('due_at', '<=', now()->addDay()->endOfDay())
            ->where('due_at', '>=', now()->subDays($maxOverdueDays)->startOfDay())
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('reminder_sent_at')
                    ->orWhere('reminder_sent_at', '<', now()->startOfDay());
            });
    }

    protected function isEligible(Loan $loan): bool
    {
        if ($loan->status !== Loan::STATUS_BORROWED || $loan->returned_at !== null || ! $loan->due_at) {
            return false;
        }

        $today = now()->startOfDay();

        if ($loan->due_at->gt(now()->addDay()->endOfDay())) {
            return false;
        }

        if ($loan->due_at->lt(now()->subDays(self::DEFAULT_MAX_OVERDUE_DAYS)->startOfDay())) {
            return false;
        }

        if ($loan->reminder_sent_at !== null && $loan->reminder_sent_at->gte($today)) {
            return false;
        }

        return true;
    }
}
