<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Loan;
use App\Models\LoanDraft;
use App\Models\LoanDraftItem;
use App\Models\LoanItem;
use App\Models\User;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoanDraftService
{
    public const TOKEN_PREFIX = 'RB-LOAN-';

    public function __construct(
        protected KioskLoanService $kioskLoanService,
    ) {}

    public function getCurrentDraft(User $user): LoanDraft
    {
        $this->expireDraftIfNeeded($user);

        $draft = $this->findCurrentDraft($user);

        if ($draft) {
            return $draft->loadMissing([
                'items.book.authors:id,name',
                'items.book.publisher:id,name',
            ]);
        }

        return LoanDraft::query()->create([
            'user_id' => $user->id,
            'status' => LoanDraft::STATUS_PENDING,
        ])->load([
            'items.book.authors:id,name',
            'items.book.publisher:id,name',
        ]);
    }

    public function addBook(User $user, int $bookId): LoanDraft
    {
        $this->ensureLoanDraftAccess($user);

        $book = Book::query()
            ->published()
            ->withCount([
                'items',
                'items as available_items_count' => fn (Builder $query): Builder => $query->available(),
            ])
            ->find($bookId);

        if (! $book) {
            throw ValidationException::withMessages([
                'book_id' => 'Buku tidak ditemukan.',
            ]);
        }

        if (! $book->is_borrowable) {
            throw ValidationException::withMessages([
                'book_id' => "Buku {$book->title} ditandai tidak boleh dipinjam.",
            ]);
        }

        if (($book->available_items_count ?? 0) < 1) {
            throw ValidationException::withMessages([
                'book_id' => 'Buku yang dipilih sedang tidak tersedia untuk dipinjam.',
            ]);
        }

        $draft = $this->getCurrentDraft($user);

        if ($draft->items()->where('book_id', $book->id)->exists()) {
            return $this->resetQrState($draft)->loadMissing([
                'items.book.authors:id,name',
                'items.book.publisher:id,name',
            ]);
        }

        $draft->items()->create([
            'book_id' => $book->id,
        ]);

        return $this->resetQrState($draft)->fresh([
            'items.book.authors:id,name',
            'items.book.publisher:id,name',
        ]);
    }

    public function removeBook(User $user, Book $book): LoanDraft
    {
        $draft = $this->getCurrentDraft($user);

        $draft->items()->where('book_id', $book->id)->delete();

        return $this->resetQrState($draft)->fresh([
            'items.book.authors:id,name',
            'items.book.publisher:id,name',
        ]);
    }

    /**
     * @param  array<int, int>  $selectedBookIds
     * @return array{draft: LoanDraft, payload: string, qr_svg: string}
     */
    public function generateQr(User $user, array $selectedBookIds): array
    {
        $this->ensureLoanDraftAccess($user);
        $this->ensureBorrowingProfileIsReady($user);

        $draft = $this->getCurrentDraft($user);
        $draft->loadMissing('items.book');

        if ($draft->items->isEmpty()) {
            throw ValidationException::withMessages([
                'draft' => 'Tambahkan minimal satu buku ke keranjang.',
            ]);
        }

        if ($draft->hasActiveToken()) {
            throw ValidationException::withMessages([
                'draft' => 'QR masih aktif. Tunggu hingga masa berlakunya berakhir.',
            ]);
        }

        $draftLimit = $this->loanMaxBooks();
        $activeLoanCount = $this->activeLoanCount($user);
        $remainingQuota = max($draftLimit - $activeLoanCount, 0);
        $normalizedSelectedBookIds = collect($selectedBookIds)
            ->map(fn (int $bookId): int => (int) $bookId)
            ->unique()
            ->values();
        $draftBookIds = $draft->items
            ->pluck('book_id')
            ->map(fn (mixed $bookId): int => (int) $bookId)
            ->values();

        if ($remainingQuota < 1) {
            throw ValidationException::withMessages([
                'book_ids' => "Member ini hanya boleh memiliki maksimal {$draftLimit} buku yang sedang dipinjam. Anda saat ini memiliki {$activeLoanCount} pinjaman aktif.",
            ]);
        }

        if ($normalizedSelectedBookIds->diff($draftBookIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'book_ids' => 'Pilih buku yang ada di keranjang.',
            ]);
        }

        if ($normalizedSelectedBookIds->count() > $remainingQuota) {
            throw ValidationException::withMessages([
                'book_ids' => "Anda hanya dapat memilih maksimal {$remainingQuota} buku untuk QR ini.",
            ]);
        }

        $selectedItems = $draft->items
            ->filter(fn (LoanDraftItem $item): bool => $normalizedSelectedBookIds->contains($item->book_id))
            ->values();

        foreach ($selectedItems as $item) {
            if (! $item->book?->is_borrowable) {
                throw ValidationException::withMessages([
                    'book_ids' => "Buku {$item->book->title} tidak lagi dapat dipinjam.",
                ]);
            }

            $isAvailable = $item->book->items()->available()->exists();

            if (! $isAvailable) {
                throw ValidationException::withMessages([
                    'book_ids' => "Buku {$item->book->title} sedang tidak tersedia untuk dipinjam.",
                ]);
            }
        }

        $plainToken = self::TOKEN_PREFIX.Str::upper(Str::random(40));
        $draft->forceFill([
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addMinutes(10),
            'status' => LoanDraft::STATUS_PENDING,
            'consumed_at' => null,
            'selected_book_ids' => $normalizedSelectedBookIds->all(),
        ])->save();

        return [
            'draft' => $draft->fresh([
                'items.book.authors:id,name',
                'items.book.publisher:id,name',
            ]),
            'payload' => $plainToken,
            'qr_svg' => $this->generateQrSvg($plainToken),
        ];
    }

    public function consume(string $payload): Loan
    {
        $token = $this->extractToken($payload);

        if ($token === null) {
            throw ValidationException::withMessages([
                'payload' => 'QR peminjaman tidak valid.',
            ]);
        }

        $existingDraft = LoanDraft::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if (! $existingDraft || $existingDraft->status !== LoanDraft::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'payload' => 'QR peminjaman tidak ditemukan atau sudah dipakai.',
            ]);
        }

        if ($existingDraft->isExpired()) {
            $existingDraft->forceFill([
                'status' => LoanDraft::STATUS_EXPIRED,
            ])->save();

            throw ValidationException::withMessages([
                'payload' => 'QR peminjaman sudah kedaluwarsa. Silakan generate ulang dari perangkat anggota.',
            ]);
        }

        return DB::transaction(function () use ($token): Loan {
            $draft = LoanDraft::query()
                ->with(['user', 'items.book'])
                ->where('token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->first();

            if (! $draft || $draft->status !== LoanDraft::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'payload' => 'QR peminjaman tidak ditemukan atau sudah dipakai.',
                ]);
            }

            if ($draft->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'payload' => 'Keranjang peminjaman ini sudah kosong.',
                ]);
            }

            $selectedBookIds = collect($draft->selected_book_ids ?: $draft->items->pluck('book_id')->all())
                ->map(fn (mixed $bookId): int => (int) $bookId)
                ->unique()
                ->values()
                ->all();

            if ($selectedBookIds === []) {
                throw ValidationException::withMessages([
                    'payload' => 'QR peminjaman ini tidak memiliki buku yang dipilih.',
                ]);
            }

            $loan = $this->kioskLoanService->borrow(
                $draft->user->email,
                $selectedBookIds,
            );

            $draft->items()
                ->whereIn('book_id', $selectedBookIds)
                ->delete();

            if ($draft->items()->exists()) {
                $this->resetQrState($draft);
            } else {
                $draft->forceFill([
                    'status' => LoanDraft::STATUS_CONSUMED,
                    'consumed_at' => now(),
                    'selected_book_ids' => null,
                ])->save();
            }

            return $loan;
        });
    }

    /**
     * @return array{count: int, maxBooks: int, activeLoansCount: int, containsBook: bool, hasActiveQr: bool}
     */
    public function summaryForBook(User $user, Book $book): array
    {
        $draft = $this->findCurrentDraft($user)?->loadMissing([
            'items.book.authors:id,name',
            'items.book.publisher:id,name',
        ]);
        $activeLoanCount = $this->activeLoanCount($user);

        return [
            'count' => $draft?->items->count() ?? 0,
            'maxBooks' => $this->loanMaxBooks(),
            'activeLoansCount' => $activeLoanCount,
            'containsBook' => $draft?->items->contains(fn (LoanDraftItem $item): bool => $item->book_id === $book->id) ?? false,
            'hasActiveQr' => $draft?->hasActiveToken() ?? false,
        ];
    }

    /**
     * @return array{count: int, maxBooks: int, activeLoansCount: int, hasActiveQr: bool, bookIds: array<int, int>}
     */
    public function summary(User $user): array
    {
        $draft = $this->findCurrentDraft($user);

        return [
            'count' => $draft?->items()->count() ?? 0,
            'maxBooks' => $this->loanMaxBooks(),
            'activeLoansCount' => $this->activeLoanCount($user),
            'hasActiveQr' => $draft?->hasActiveToken() ?? false,
            'bookIds' => $draft?->items()->pluck('book_id')->map(fn (mixed $bookId): int => (int) $bookId)->all() ?? [],
        ];
    }

    public function loanMaxBooks(): int
    {
        return $this->kioskLoanService->loanMaxBooks();
    }

    protected function ensureLoanDraftAccess(User $user): void
    {
        $user->assignMemberRoleIfAvailable();

        if (! $user->canBorrowBooks()) {
            throw ValidationException::withMessages([
                'draft' => 'Layanan peminjaman tersedia untuk mahasiswa Teknik Informatika yang terdaftar.',
            ]);
        }
    }

    protected function ensureBorrowingProfileIsReady(User $user): void
    {
        if (! $user->hasRequiredProfileDetails()) {
            throw ValidationException::withMessages([
                'draft' => 'Nomor WhatsApp dan alamat wajib diisi pada profil sebelum meminjam buku.',
            ]);
        }

        $restrictionMessage = $this->kioskLoanService->borrowingRestrictionMessage($user);

        if ($restrictionMessage !== null) {
            throw ValidationException::withMessages([
                'draft' => $restrictionMessage,
            ]);
        }
    }

    protected function activeLoanCount(User $user): int
    {
        return LoanItem::query()
            ->whereNull('returned_at', 'and', false)
            ->whereHas('loan', fn (Builder $query): Builder => $query
                ->whereBelongsTo($user)
                ->where('status', Loan::STATUS_BORROWED))
            ->count();
    }

    protected function findCurrentDraft(User $user): ?LoanDraft
    {
        return LoanDraft::query()
            ->whereBelongsTo($user)
            ->pending()
            ->latest('id')
            ->first();
    }

    protected function resetQrState(LoanDraft $draft): LoanDraft
    {
        $draft->forceFill([
            'token_hash' => null,
            'expires_at' => null,
            'consumed_at' => null,
            'status' => LoanDraft::STATUS_PENDING,
            'selected_book_ids' => null,
        ])->save();

        return $draft;
    }

    protected function expireDraftIfNeeded(User $user): void
    {
        LoanDraft::query()
            ->whereBelongsTo($user)
            ->pending()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update([
                'status' => LoanDraft::STATUS_EXPIRED,
            ]);
    }

    protected function extractToken(string $payload): ?string
    {
        $normalized = Str::of($payload)->trim()->toString();

        if ($normalized === '') {
            return null;
        }

        if (Str::startsWith($normalized, self::TOKEN_PREFIX)) {
            return $normalized;
        }

        if (filter_var($normalized, FILTER_VALIDATE_URL) !== false) {
            $query = parse_url($normalized, PHP_URL_QUERY);

            if (! is_string($query)) {
                return null;
            }

            parse_str($query, $queryParams);

            $token = $queryParams['token'] ?? null;

            return is_string($token) && Str::startsWith($token, self::TOKEN_PREFIX)
                ? $token
                : null;
        }

        return null;
    }

    protected function generateQrSvg(string $payload): string
    {
        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle(192, 0, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(17, 24, 39))),
                new SvgImageBackEnd
            )
        ))->writeString($payload);

        return trim(substr($svg, strpos($svg, "\n") + 1));
    }
}
