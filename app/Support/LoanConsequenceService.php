<?php

namespace App\Support;

use App\Models\Loan;
use App\Models\User;
use App\Repositories\SettingRepository;
use Carbon\CarbonInterface;

class LoanConsequenceService
{
    /**
     * @var array<string, array{label: string, color: string, detail: string|null}>
     */
    protected array $summaryCache = [];

    public function __construct(
        protected SettingRepository $settingRepository,
    ) {}

    /**
     * @return array{label: string, color: string, detail: string|null}
     */
    public function borrowingAccessSummary(User $user): array
    {
        $cacheKey = (string) ($user->getKey() ?? spl_object_id($user));

        return $this->summaryCache[$cacheKey] ??= $this->resolveBorrowingAccessSummary($user);
    }

    public function borrowingRestrictionMessage(User $user): ?string
    {
        $cacheKey = (string) ($user->getKey() ?? spl_object_id($user));

        if (isset($this->summaryCache[$cacheKey]) && $this->summaryCache[$cacheKey]['label'] === 'Dibatasi') {
            return $this->summaryCache[$cacheKey]['detail'];
        }

        return $this->resolveBorrowingRestrictionMessage($user);
    }

    public function lateReturnSuspensionEnabled(): bool
    {
        return filter_var(
            $this->settingRepository->get('library', 'late_return_suspension_enabled', '1'),
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE,
        ) ?? true;
    }

    public function lateReturnSuspendAfterDays(): int
    {
        return max((int) $this->settingRepository->get('library', 'late_return_suspend_after_days', 1), 1);
    }

    public function lateReturnCooldownDays(): int
    {
        return max((int) $this->settingRepository->get('library', 'late_return_cooldown_days', 3), 0);
    }

    protected function cooldownEndsAt(Loan $loan, int $cooldownDays): ?CarbonInterface
    {
        if (! $loan->returned_at instanceof CarbonInterface) {
            return null;
        }

        return $loan->returned_at->copy()->addDays($cooldownDays);
    }

    /**
     * @return array{label: string, color: string, detail: string|null}
     */
    protected function resolveBorrowingAccessSummary(User $user): array
    {
        $detail = $this->resolveBorrowingRestrictionMessage($user);

        if ($detail !== null) {
            return [
                'label' => 'Dibatasi',
                'color' => 'danger',
                'detail' => $detail,
            ];
        }

        return [
            'label' => 'Normal',
            'color' => 'success',
            'detail' => 'Anggota dapat melakukan peminjaman baru.',
        ];
    }

    protected function resolveBorrowingRestrictionMessage(User $user): ?string
    {
        if (! $this->lateReturnSuspensionEnabled()) {
            return null;
        }

        $thresholdDays = $this->lateReturnSuspendAfterDays();

        $activeOverdueLoan = Loan::query()
            ->whereBelongsTo($user)
            ->where('status', Loan::STATUS_BORROWED)
            ->whereNotNull('due_at')
            ->get()
            ->first(fn (Loan $loan): bool => $loan->lateDays() >= $thresholdDays);

        if ($activeOverdueLoan) {
            return "Akun ini sedang dibatasi karena memiliki pinjaman yang terlambat {$activeOverdueLoan->lateDays()} hari. Kembalikan seluruh buku yang melewati batas pengembalian untuk meminjam lagi.";
        }

        $cooldownDays = $this->lateReturnCooldownDays();

        if ($cooldownDays < 1) {
            return null;
        }

        $latestLateReturn = Loan::query()
            ->whereBelongsTo($user)
            ->where('status', Loan::STATUS_RETURNED)
            ->whereNotNull('due_at')
            ->whereNotNull('returned_at')
            ->latest('returned_at')
            ->get()
            ->first(function (Loan $loan) use ($cooldownDays, $thresholdDays): bool {
                $availableAt = $this->cooldownEndsAt($loan, $cooldownDays);

                return $loan->lateDays() >= $thresholdDays
                    && $availableAt instanceof CarbonInterface
                    && $availableAt->isFuture();
            });

        if (! $latestLateReturn) {
            return null;
        }

        $availableAt = $this->cooldownEndsAt($latestLateReturn, $cooldownDays);

        if (! $availableAt instanceof CarbonInterface) {
            return null;
        }

        return 'Akun ini sedang dibatasi karena pengembalian buku terlambat. Peminjaman dapat dilakukan kembali mulai '
            .$availableAt->translatedFormat('d F Y H:i').'.';
    }
}
