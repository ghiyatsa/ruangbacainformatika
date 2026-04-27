<?php

namespace App\Support\Library;

use App\Models\BookItem;
use App\Models\KioskDevice;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\User;
use App\Support\Settings\SettingRepository;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KioskLoanService
{
    public function __construct(
        protected SettingRepository $settingRepository,
    ) {}

    public function borrow(?KioskDevice $kioskDevice, string $memberIdentifier, string $isbn): Loan
    {
        $member = $this->resolveMember($memberIdentifier);

        if (! $member || ! $member->hasRole('member')) {
            throw ValidationException::withMessages([
                'member_identifier' => 'Member tidak ditemukan atau belum terdaftar sebagai member.',
            ]);
        }

        if (! filled($member->whatsapp)) {
            throw ValidationException::withMessages([
                'member_identifier' => 'Nomor WhatsApp wajib diisi pada profil sebelum meminjam buku.',
            ]);
        }

        $loanLimit = $this->loanMaxBooks();
        $activeLoanCount = $this->activeLoanCount($member);

        if (($activeLoanCount + 1) > $loanLimit) {
            throw ValidationException::withMessages([
                'isbn' => "Member ini hanya boleh memiliki maksimal {$loanLimit} buku yang sedang dipinjam.",
            ]);
        }

        return DB::transaction(function () use ($kioskDevice, $member, $isbn): Loan {
            $borrowedAt = now();

            $loan = Loan::query()->create([
                'user_id' => $member->id,
                'kiosk_device_id' => $kioskDevice?->id,
                'status' => Loan::STATUS_BORROWED,
                'borrowed_at' => $borrowedAt,
                'due_at' => $this->calculateDueAt($borrowedAt),
            ]);

            $bookItem = BookItem::query()
                ->with('book')
                ->available()
                ->whereHas('book', fn ($query) => $query->where('isbn', $isbn))
                ->lockForUpdate()
                ->first();

            if (! $bookItem) {
                throw ValidationException::withMessages([
                    'isbn' => "Buku dengan ISBN {$isbn} sedang tidak tersedia untuk dipinjam.",
                ]);
            }

            $loan->items()->create([
                'book_item_id' => $bookItem->id,
            ]);

            $bookItem->forceFill([
                'status' => 'borrowed',
            ])->save();

            return $loan->load('items.bookItem.book', 'user');
        });
    }

    public function returnBooks(string $memberIdentifier, string $isbn): int
    {
        $member = $this->resolveMember($memberIdentifier);

        if (! $member || ! $member->hasRole('member')) {
            throw ValidationException::withMessages([
                'member_identifier' => 'Member tidak ditemukan atau belum terdaftar sebagai member.',
            ]);
        }

        return DB::transaction(function () use ($member, $isbn): int {
            $loanItem = LoanItem::query()
                ->whereNull('returned_at')
                ->whereHas('bookItem.book', fn ($query) => $query->where('isbn', $isbn))
                ->whereHas('loan', function ($query) use ($member) {
                    $query
                        ->whereBelongsTo($member)
                        ->where('status', Loan::STATUS_BORROWED);
                })
                ->with(['loan.items', 'bookItem'])
                ->lockForUpdate()
                ->first();

            if (! $loanItem) {
                throw ValidationException::withMessages([
                    'isbn' => "Tidak ada peminjaman aktif untuk ISBN {$isbn} atas member tersebut.",
                ]);
            }

            $loanItem->forceFill([
                'returned_at' => now(),
            ])->save();

            $loanItem->bookItem->forceFill([
                'status' => 'available',
            ])->save();

            $loan = $loanItem->loan->fresh('items');

            if ($loan && $loan->items->every(fn (LoanItem $item): bool => $item->isReturned())) {
                $loan->forceFill([
                    'status' => Loan::STATUS_RETURNED,
                    'returned_at' => now(),
                ])->save();
            }

            return 1;
        });
    }

    public function loanMaxBooks(): int
    {
        return max((int) $this->settingRepository->get('general', 'loan_max_books', 3), 1);
    }

    public function loanDurationDays(): int
    {
        return max((int) $this->settingRepository->get('general', 'loan_duration_days', 5), 1);
    }

    protected function activeLoanCount(User $user): int
    {
        return LoanItem::query()
            ->whereNull('returned_at')
            ->whereHas('loan', fn ($query) => $query->whereBelongsTo($user))
            ->count();
    }

    protected function resolveMember(string $memberIdentifier): ?User
    {
        if (str($memberIdentifier)->contains('@')) {
            return User::query()
                ->where('email', $memberIdentifier)
                ->first();
        }

        return User::query()
            ->where('email', 'like', '%'.$memberIdentifier.'@mhs.unimal.ac.id')
            ->first();
    }

    protected function calculateDueAt(CarbonInterface $borrowedAt): Carbon
    {
        return Carbon::parse($borrowedAt)->addWeekdays($this->loanDurationDays());
    }
}
