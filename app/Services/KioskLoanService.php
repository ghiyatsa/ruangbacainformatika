<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\User;
use App\Notifications\LoanReceiptDatabaseNotification;
use App\Notifications\LoanReceiptNotification;
use App\Notifications\LoanReturnNotification;
use App\Repositories\SettingRepository;
use App\Support\CampusEmail;
use App\Support\LoanConsequenceService;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class KioskLoanService
{
    public function __construct(
        protected SettingRepository $settingRepository,
        protected CampusEmail $campusEmail,
        protected LoanConsequenceService $loanConsequenceService,
    ) {}

    /**
     * @param  array<int, int>  $bookIds
     */
    public function borrow(string $memberIdentifier, array $bookIds): Loan
    {
        $member = $this->findMemberByIdentifier($memberIdentifier);

        if (! $member || ! $member->canBorrowBooks()) {
            throw ValidationException::withMessages([
                'member_identifier' => 'Anggota tidak ditemukan atau tidak memiliki akses peminjaman.',
            ]);
        }

        if (! $member->hasRequiredProfileDetails()) {
            throw ValidationException::withMessages([
                'member_identifier' => 'Nomor WhatsApp dan alamat wajib diisi pada profil sebelum meminjam buku.',
            ]);
        }

        $restrictionMessage = $this->borrowingRestrictionMessage($member);

        if ($restrictionMessage !== null) {
            throw ValidationException::withMessages([
                'member_identifier' => $restrictionMessage,
            ]);
        }

        $loanLimit = $this->loanMaxBooks();
        $activeLoanCount = $this->activeLoanCount($member);
        $requestCount = count($bookIds);

        if (($activeLoanCount + $requestCount) > $loanLimit) {
            throw ValidationException::withMessages([
                'book_ids' => "Member ini hanya boleh memiliki maksimal {$loanLimit} buku yang sedang dipinjam. Anda saat ini memiliki {$activeLoanCount} pinjaman aktif.",
            ]);
        }

        $loan = DB::transaction(function () use ($member, $bookIds): Loan {
            $borrowedAt = now();

            $loan = Loan::query()->create([
                'user_id' => $member->id,
                'status' => Loan::STATUS_BORROWED,
                'borrowed_at' => $borrowedAt,
                'due_at' => $this->calculateDueAt($borrowedAt),
            ]);

            foreach ($bookIds as $index => $bookId) {
                $bookItem = BookItem::query()
                    ->available()
                    ->whereHas('book', fn ($query) => $query
                        ->whereKey($bookId)
                        ->where('is_borrowable', true))
                    ->lockForUpdate()
                    ->first();

                $book = Book::query()->find($bookId);

                if (! $bookItem) {
                    if ($book && ! $book->is_borrowable) {
                        throw ValidationException::withMessages([
                            "book_ids.{$index}" => "Buku {$book->title} ditandai tidak boleh dipinjam.",
                        ]);
                    }

                    throw ValidationException::withMessages([
                        "book_ids.{$index}" => 'Buku yang dipilih sedang tidak tersedia untuk dipinjam.',
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

        // Pengiriman notifikasi tidak boleh menggagalkan transaksi peminjaman.
        try {
            $member->notify(new LoanReceiptDatabaseNotification($loan));
            $member->notify(new LoanReceiptNotification($loan));
        } catch (Throwable $exception) {
            report($exception);
        }

        return $loan;
    }

    /**
     * Kembalikan buku berdasarkan daftar ISBN.
     *
     * @param  array<int, string>  $isbns
     */
    public function returnBooks(string $memberIdentifier, array $isbns): int
    {
        $member = $this->findMemberByIdentifier($memberIdentifier);

        if (! $member || ! $member->canBorrowBooks()) {
            throw ValidationException::withMessages([
                'member_identifier' => 'Member tidak ditemukan atau tidak memiliki akses peminjaman.',
            ]);
        }

        $result = DB::transaction(function () use ($member, $isbns): array {
            $returnedBookTitles = [];

            foreach ($isbns as $index => $isbn) {
                $loanItem = LoanItem::query()
                    ->whereNull('returned_at', 'and', false)
                    ->whereHas('bookItem.book', fn ($query) => $query->where('isbn', $isbn))
                    ->whereHas('loan', function ($query) use ($member) {
                        $query
                            ->whereBelongsTo($member)
                            ->where('status', Loan::STATUS_BORROWED);
                    })
                    ->with(['loan.items', 'bookItem.book'])
                    ->lockForUpdate()
                    ->first();

                if (! $loanItem) {
                    throw ValidationException::withMessages([
                        "isbns.{$index}" => "Tidak ada peminjaman aktif untuk ISBN {$isbn} atas member tersebut.",
                    ]);
                }

                $returnedBookTitles[] = $this->processReturn($loanItem);
            }

            return [
                'returned_count' => count($returnedBookTitles),
                'returned_book_titles' => $returnedBookTitles,
            ];
        });

        $this->sendReturnNotification($member, $result['returned_book_titles']);

        return $result['returned_count'];
    }

    /**
     * Kembalikan buku berdasarkan daftar Book ID.
     *
     * @param  array<int, int>  $bookIds
     */
    public function returnBooksByBookIds(string $memberIdentifier, array $bookIds): int
    {
        $member = $this->findMemberByIdentifier($memberIdentifier);

        if (! $member || ! $member->canBorrowBooks()) {
            throw ValidationException::withMessages([
                'member_identifier' => 'Member tidak ditemukan atau tidak memiliki akses peminjaman.',
            ]);
        }

        $result = DB::transaction(function () use ($member, $bookIds): array {
            $returnedBookTitles = [];

            foreach ($bookIds as $index => $bookId) {
                $loanItem = LoanItem::query()
                    ->whereNull('returned_at', 'and', false)
                    ->whereHas('bookItem.book', fn ($query) => $query->whereKey($bookId))
                    ->whereHas('loan', function ($query) use ($member) {
                        $query
                            ->whereBelongsTo($member)
                            ->where('status', Loan::STATUS_BORROWED);
                    })
                    ->with(['loan.items', 'bookItem', 'bookItem.book'])
                    ->lockForUpdate()
                    ->first();

                if (! $loanItem) {
                    throw ValidationException::withMessages([
                        "book_ids.{$index}" => 'Buku yang dipilih tidak tercatat sebagai pinjaman aktif untuk anggota ini.',
                    ]);
                }

                $returnedBookTitles[] = $this->processReturn($loanItem);
            }

            return [
                'returned_count' => count($returnedBookTitles),
                'returned_book_titles' => $returnedBookTitles,
            ];
        });

        $this->sendReturnNotification($member, $result['returned_book_titles']);

        return $result['returned_count'];
    }

    /**
     * Kembalikan buku berdasarkan daftar Loan Item ID.
     *
     * @param  array<int, int>  $loanItemIds
     */
    public function returnBooksByLoanItemIds(string $memberIdentifier, array $loanItemIds): int
    {
        $member = $this->findMemberByIdentifier($memberIdentifier);

        if (! $member || ! $member->canBorrowBooks()) {
            throw ValidationException::withMessages([
                'member_identifier' => 'Member tidak ditemukan atau tidak memiliki akses peminjaman.',
            ]);
        }

        $result = DB::transaction(function () use ($member, $loanItemIds): array {
            $returnedBookTitles = [];

            foreach ($loanItemIds as $index => $loanItemId) {
                $loanItem = LoanItem::query()
                    ->whereKey($loanItemId)
                    ->whereNull('returned_at', 'and', false)
                    ->whereHas('loan', function ($query) use ($member) {
                        $query
                            ->whereBelongsTo($member)
                            ->where('status', Loan::STATUS_BORROWED);
                    })
                    ->with(['loan.items', 'bookItem', 'bookItem.book'])
                    ->lockForUpdate()
                    ->first();

                if (! $loanItem) {
                    throw ValidationException::withMessages([
                        "loan_item_ids.{$index}" => 'Buku yang dipilih tidak lagi tercatat sebagai pinjaman aktif untuk anggota ini.',
                    ]);
                }

                $returnedBookTitles[] = $this->processReturn($loanItem);
            }

            return [
                'returned_count' => count($returnedBookTitles),
                'returned_book_titles' => $returnedBookTitles,
            ];
        });

        $this->sendReturnNotification($member, $result['returned_book_titles']);

        return $result['returned_count'];
    }

    /**
     * Proses pengembalian satu item peminjaman.
     * Mengembalikan judul buku yang berhasil dikembalikan.
     */
    protected function processReturn(LoanItem $loanItem): string
    {
        $loanItem->loadMissing('bookItem.book', 'loan.items');

        $bookTitle = $loanItem->bookItem->book->title ?? 'Buku Tanpa Judul';

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

        return $bookTitle;
    }

    public function loanMaxBooks(): int
    {
        return max((int) $this->settingRepository->get('library', 'loan_max_books', 3), 1);
    }

    public function loanDurationDays(): int
    {
        return max((int) $this->settingRepository->get('library', 'loan_duration_days', 5), 1);
    }

    public function borrowingRestrictionMessage(User $user): ?string
    {
        return $this->loanConsequenceService->borrowingRestrictionMessage($user);
    }

    protected function activeLoanCount(User $user): int
    {
        return LoanItem::query()
            ->whereNull('returned_at', 'and', false)
            ->whereHas('loan', fn ($query) => $query->whereBelongsTo($user))
            ->count();
    }

    public function findMemberByIdentifier(string $memberIdentifier): ?User
    {
        $normalizedIdentifier = str($memberIdentifier)->trim()->lower()->toString();

        if (str($normalizedIdentifier)->contains('@')) {
            return User::query()
                ->where('email', $normalizedIdentifier)
                ->first();
        }

        $phoneDigits = preg_replace('/\D+/', '', $normalizedIdentifier);
        if ($phoneDigits !== '') {
            $possibleNumbers = [];
            if (str_starts_with($phoneDigits, '08')) {
                $possibleNumbers = [$phoneDigits, '62'.substr($phoneDigits, 1)];
            } elseif (str_starts_with($phoneDigits, '628')) {
                $possibleNumbers = [$phoneDigits, '0'.substr($phoneDigits, 2)];
            } elseif (str_starts_with($phoneDigits, '8')) {
                $possibleNumbers = ['0'.$phoneDigits, '62'.$phoneDigits];
            } else {
                $possibleNumbers = [$phoneDigits];
            }

            $user = User::query()
                ->whereIn('whatsapp', $possibleNumbers)
                ->first();

            if ($user) {
                return $user;
            }
        }

        if (! preg_match('/^\d{9}$/', $normalizedIdentifier)) {
            return null;
        }

        $matches = User::query()
            ->where('email', 'like', '%'.$normalizedIdentifier.'@mhs.unimal.ac.id')
            ->get()
            ->filter(fn (User $user): bool => $this->campusEmail->extractIdentityNumber($user->email) === $normalizedIdentifier)
            ->values();

        if ($matches->count() !== 1) {
            return null;
        }

        return $matches->first();
    }

    protected function calculateDueAt(CarbonInterface $borrowedAt): Carbon
    {
        return Carbon::parse($borrowedAt)->addWeekdays($this->loanDurationDays());
    }

    /**
     * @param  list<string>  $returnedBookTitles
     */
    protected function sendReturnNotification(User $member, array $returnedBookTitles): void
    {
        if ($returnedBookTitles === []) {
            return;
        }

        try {
            $member->notify(new LoanReturnNotification($returnedBookTitles, now()->translatedFormat('d F Y H:i')));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
