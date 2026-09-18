<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Loan;
use App\Models\User;
use App\Notifications\LoanReminderDatabaseNotification;
use App\Notifications\LoanReminderNotification;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class LoanReminderService
{
    /**
     * Ambang jatuh tempo yang membuka izin pengingat: H-1, atau H+3 pada Jumat.
     * Dipakai bersama oleh daftar peminjam dan pengirim pengingat agar
     * pinjaman yang tampil selalu bisa diingatkan.
     */
    public static function reminderDueThreshold(): CarbonImmutable
    {
        return now()->isFriday()
            ? now()->addDays(3)->endOfDay()->toImmutable()
            : now()->addDay()->endOfDay()->toImmutable();
    }

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
     * Query pinjaman yang layak diingatkan: sudah jatuh tempo H-1 atau sudah
     * telat berapa pun lamanya, dan belum diingatkan hari ini.
     *
     * @return Builder<Loan>
     */
    public function eligibleLoansQuery(): Builder
    {
        return Loan::query()
            ->where('status', Loan::STATUS_BORROWED)
            ->whereNull('returned_at')
            ->whereNotNull('due_at')
            ->where('due_at', '<=', self::reminderDueThreshold())
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

        if ($loan->due_at->gt(self::reminderDueThreshold())) {
            return false;
        }

        if ($loan->reminder_sent_at !== null && $loan->reminder_sent_at->gte($today)) {
            return false;
        }

        return true;
    }
}
