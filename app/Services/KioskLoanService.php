<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\User;
use App\Notifications\LoanReceiptNotification;
use App\Repositories\SettingRepository;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KioskLoanService
{
    public function __construct(
        protected SettingRepository $settingRepository,
    ) {}

    /**
     * @param  array<int, string>  $isbns
     */
    public function borrow(string $memberIdentifier, array $isbns): Loan
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
        $requestCount = count($isbns);

        if (($activeLoanCount + $requestCount) > $loanLimit) {
            throw ValidationException::withMessages([
                'isbns' => "Member ini hanya boleh memiliki maksimal {$loanLimit} buku yang sedang dipinjam. Anda saat ini memiliki {$activeLoanCount} pinjaman aktif.",
            ]);
        }

        $loan = DB::transaction(function () use ($member, $isbns): Loan {
            $borrowedAt = now();

            $loan = Loan::query()->create([
                'user_id' => $member->id,
                'status' => Loan::STATUS_BORROWED,
                'borrowed_at' => $borrowedAt,
                'due_at' => $this->calculateDueAt($borrowedAt),
            ]);

            foreach ($isbns as $index => $isbn) {
                $bookItem = BookItem::query()
                    ->available()
                    ->whereHas('book', fn ($query) => $query
                        ->where('isbn', $isbn)
                        ->where('is_borrowable', true))
                    ->lockForUpdate()
                    ->first();

                if (! $bookItem) {
                    $book = Book::query()->where('isbn', $isbn)->first();

                    if ($book && ! $book->is_borrowable) {
                        throw ValidationException::withMessages([
                            "isbns.{$index}" => "Buku dengan ISBN {$isbn} ditandai tidak boleh dipinjam.",
                        ]);
                    }

                    throw ValidationException::withMessages([
                        "isbns.{$index}" => "Buku dengan ISBN {$isbn} sedang tidak tersedia untuk dipinjam.",
                    ]);
                }

                $loan->items()->create([
                    'book_item_id' => $bookItem->id,
                ]);

                $bookItem->forceFill([
                    'status' => 'borrowed',
                ])->save();
            }

            return $loan->load('items.bookItem.book', 'user');
        });

        // Kirim email notifikasi bukti peminjaman
        $member->notify(new LoanReceiptNotification($loan));

        return $loan;
    }

    /**
     * @param  array<int, string>  $isbns
     */
    public function returnBooks(string $memberIdentifier, array $isbns): int
    {
        $member = $this->resolveMember($memberIdentifier);

        if (! $member || ! $member->hasRole('member')) {
            throw ValidationException::withMessages([
                'member_identifier' => 'Member tidak ditemukan atau belum terdaftar sebagai member.',
            ]);
        }

        return DB::transaction(function () use ($member, $isbns): int {
            $returnedCount = 0;

            foreach ($isbns as $index => $isbn) {
                $loanItem = LoanItem::query()
                    ->whereNull('returned_at', 'and', false)
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
                        "isbns.{$index}" => "Tidak ada peminjaman aktif untuk ISBN {$isbn} atas member tersebut.",
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

                $returnedCount++;
            }

            return $returnedCount;
        });
    }

    public function loanMaxBooks(): int
    {
        return max((int) $this->settingRepository->get('library', 'loan_max_books', 3), 1);
    }

    public function loanDurationDays(): int
    {
        return max((int) $this->settingRepository->get('library', 'loan_duration_days', 5), 1);
    }

    protected function activeLoanCount(User $user): int
    {
        return LoanItem::query()
            ->whereNull('returned_at', 'and', false)
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
