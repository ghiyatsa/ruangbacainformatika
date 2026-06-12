<?php

namespace App\Services;

use App\Models\LoanItem;
use App\Models\ReturnDraft;
use App\Models\User;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Eye\SimpleCircleEye;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Module\RoundnessModule;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReturnDraftService
{
    public const TOKEN_PREFIX = 'RB-RETURN-';

    public const SHORT_TOKEN_PREFIX = 'RB-';

    protected const OPAQUE_TOKEN_LENGTH = 48;

    public function __construct(
        protected KioskLoanService $kioskLoanService,
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

        $plainToken = $this->makeToken();
        $draft->forceFill([
            'token_hash' => hash('sha256', $plainToken),
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
            'qr_svg' => $this->generateQrSvg($plainToken),
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
            ->where('token_hash', hash('sha256', $token))
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
                ->where('token_hash', hash('sha256', $token))
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
        $normalized = Str::of($payload)->trim()->toString();

        if ($normalized === '') {
            return null;
        }

        if ($this->isReadableToken($normalized)) {
            return $normalized;
        }

        if (filter_var($normalized, FILTER_VALIDATE_URL) !== false) {
            $query = parse_url($normalized, PHP_URL_QUERY);

            if (! is_string($query)) {
                return null;
            }

            parse_str($query, $queryParams);

            $token = $queryParams['token'] ?? null;

            return is_string($token) && $this->isReadableToken($token)
                ? $token
                : null;
        }

        return null;
    }

    protected function makeToken(): string
    {
        return self::SHORT_TOKEN_PREFIX.Str::random(self::OPAQUE_TOKEN_LENGTH);
    }

    protected function isReadableToken(string $token): bool
    {
        if (Str::startsWith($token, [self::TOKEN_PREFIX, self::SHORT_TOKEN_PREFIX])) {
            return true;
        }

        return preg_match('/\A[A-Za-z0-9]{80,160}\z/', $token) === 1;
    }

    protected function generateQrSvg(string $payload): string
    {
        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle(
                    192,
                    0,
                    new RoundnessModule(0.8),
                    SimpleCircleEye::instance(),
                    Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(17, 24, 39))
                ),
                new SvgImageBackEnd
            )
        ))->writeString($payload);

        $svg = trim(substr($svg, strpos($svg, "\n") + 1));

        // Replace fill colors to use currentColor for frontend theme compatibility
        $svg = (string) preg_replace('/<rect\b([^>]*)fill="#ffffff"([^>]*)><\/rect>/i', '<rect$1fill="transparent"$2></rect>', $svg);
        $svg = (string) preg_replace('/<path\b([^>]*)fill="#111827"([^>]*)>/i', '<path$1fill="currentColor"$2>', $svg);
        $svg = (string) preg_replace('/fill="#ffffff"/i', 'fill="transparent"', $svg);
        $svg = (string) preg_replace('/fill="#111827"/i', 'fill="currentColor"', $svg);

        return str_replace('<svg ', '<svg color="currentColor" ', $svg);
    }
}
