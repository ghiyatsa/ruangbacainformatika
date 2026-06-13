<?php

namespace App\Services;

use App\Models\LoanItem;
use App\Models\ReturnDraft;
use App\Models\User;
use App\Services\Borrowing\LoanQrCodeService;
use App\Services\Borrowing\LoanTokenService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnDraftService
{
    public const TOKEN_PREFIX = 'RB-RETURN-';

    public const SHORT_TOKEN_PREFIX = 'RB-';

    public function __construct(
        protected KioskLoanService $kioskLoanService,
        protected LoanQrCodeService $loanQrCodeService,
        protected LoanTokenService $loanTokenService,
    ) {}

    public function getCurrentDraft(User $user): ?ReturnDraft
    {
        $this->expireDraftIfNeeded($user);

        return $this->findCurrentDraft($user)?->loadMissing([
            'items.loanItem.loan',
            'items.loanItem.bookItem.book',
        ]);
    }

    /**
     * @param  array<int, int>  $selectedLoanItemIds
     * @return array{draft: ReturnDraft, payload: string, qr_svg: string}
     */
    public function generateQr(User $user, array $selectedLoanItemIds): array
    {
        $normalizedLoanItemIds = collect($selectedLoanItemIds)
            ->map(fn (int $loanItemId): int => (int) $loanItemId)
            ->unique()
            ->values();

        if ($normalizedLoanItemIds->isEmpty()) {
            throw ValidationException::withMessages([
                'loan_item_ids' => 'Pilih minimal satu buku aktif untuk dikembalikan.',
            ]);
        }

        $existingDraft = $this->getCurrentDraft($user);

        if ($existingDraft?->hasActiveToken()) {
            throw ValidationException::withMessages([
                'draft' => 'QR pengembalian masih aktif. Tunggu hingga masa berlakunya berakhir.',
            ]);
        }

        $selectedLoanItems = LoanItem::query()
            ->with(['loan', 'bookItem.book'])
            ->whereIn('id', $normalizedLoanItemIds->all())
            ->whereNull('returned_at', 'and', false)
            ->whereHas('loan', fn ($query) => $query
                ->whereBelongsTo($user))
            ->get();

        if ($selectedLoanItems->count() !== $normalizedLoanItemIds->count()) {
            throw ValidationException::withMessages([
                'loan_item_ids' => 'Sebagian buku yang dipilih tidak lagi tercatat sebagai pinjaman aktif.',
            ]);
        }

        $draft = $existingDraft ?? ReturnDraft::query()->create([
            'user_id' => $user->id,
            'status' => ReturnDraft::STATUS_PENDING,
        ]);

        $draft->items()->delete();

        foreach ($selectedLoanItems as $loanItem) {
            $draft->items()->create([
                'loan_item_id' => $loanItem->id,
            ]);
        }

        $plainToken = $this->loanTokenService->make(self::SHORT_TOKEN_PREFIX);
        $draft->forceFill([
            'token_hash' => $this->loanTokenService->hash($plainToken),
            'expires_at' => now()->addMinutes(10),
            'status' => ReturnDraft::STATUS_PENDING,
            'consumed_at' => null,
            'selected_loan_item_ids' => $normalizedLoanItemIds->all(),
        ])->save();

        return [
            'draft' => $draft->fresh([
                'items.loanItem.loan',
                'items.loanItem.bookItem.book',
            ]),
            'payload' => $plainToken,
            'qr_svg' => $this->loanQrCodeService->generateSvg($plainToken),
        ];
    }

    public function consume(string $payload): int
    {
        $token = $this->extractToken($payload);

        if ($token === null) {
            throw ValidationException::withMessages([
                'payload' => 'QR pengembalian tidak valid.',
            ]);
        }

        $existingDraft = ReturnDraft::query()
            ->with('user')
            ->where('token_hash', $this->loanTokenService->hash($token))
            ->first();

        if (! $existingDraft || $existingDraft->status !== ReturnDraft::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'payload' => 'QR pengembalian tidak ditemukan atau sudah dipakai.',
            ]);
        }

        if ($existingDraft->isExpired()) {
            $existingDraft->forceFill([
                'status' => ReturnDraft::STATUS_EXPIRED,
            ])->save();

            throw ValidationException::withMessages([
                'payload' => 'QR pengembalian sudah kedaluwarsa. Silakan generate ulang dari perangkat anggota.',
            ]);
        }

        return DB::transaction(function () use ($token): int {
            $draft = ReturnDraft::query()
                ->with(['user', 'items.loanItem.bookItem.book', 'items.loanItem.loan'])
                ->where('token_hash', $this->loanTokenService->hash($token))
                ->lockForUpdate()
                ->first();

            if (! $draft || $draft->status !== ReturnDraft::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'payload' => 'QR pengembalian tidak ditemukan atau sudah dipakai.',
                ]);
            }

            if ($draft->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'payload' => 'QR pengembalian ini tidak memiliki buku yang dipilih.',
                ]);
            }

            $selectedLoanItemIds = collect($draft->selected_loan_item_ids ?: $draft->items->pluck('loan_item_id')->all())
                ->map(fn (mixed $loanItemId): int => (int) $loanItemId)
                ->unique()
                ->values()
                ->all();

            if ($selectedLoanItemIds === []) {
                throw ValidationException::withMessages([
                    'payload' => 'QR pengembalian ini tidak memiliki buku yang dipilih.',
                ]);
            }

            $returnedCount = $this->kioskLoanService->returnBooksByLoanItemIds(
                $draft->user->email,
                $selectedLoanItemIds,
            );

            $draft->items()->delete();
            $draft->forceFill([
                'status' => ReturnDraft::STATUS_CONSUMED,
                'consumed_at' => now(),
                'selected_loan_item_ids' => null,
            ])->save();

            return $returnedCount;
        });
    }

    protected function findCurrentDraft(User $user): ?ReturnDraft
    {
        return ReturnDraft::query()
            ->whereBelongsTo($user)
            ->pending()
            ->latest('id')
            ->first();
    }

    protected function expireDraftIfNeeded(User $user): void
    {
        ReturnDraft::query()
            ->whereBelongsTo($user)
            ->pending()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update([
                'status' => ReturnDraft::STATUS_EXPIRED,
            ]);
    }

    protected function extractToken(string $payload): ?string
    {
        return $this->loanTokenService->extract($payload, [
            self::TOKEN_PREFIX,
            self::SHORT_TOKEN_PREFIX,
        ]);
    }
}
