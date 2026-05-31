<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanDraftBookRequest;
use App\Http\Requests\LoanDraftQrRequest;
use App\Models\Book;
use App\Models\LoanDraft;
use App\Models\LoanDraftItem;
use App\Services\LoanDraftService;
use App\Support\AppTimezone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoanRequestController extends Controller
{
    public function __construct(
        protected LoanDraftService $loanDraftService,
    ) {}

    public function show(Request $request): Response|RedirectResponse
    {
        if (! $request->user()->canBorrowBooks()) {
            Inertia::flash('toast', [
                'type' => 'info',
                'message' => 'Layanan peminjaman tersedia untuk mahasiswa Teknik Informatika yang terdaftar.',
            ]);

            return redirect()->route('home');
        }

        $draft = $this->loanDraftService->getCurrentDraft($request->user());
        $summary = $this->loanDraftService->summary($request->user());

        return Inertia::render('loans/request', [
            'draft' => $this->toDraftPayload($draft),
            'stats' => [
                'loanMaxBooks' => $summary['maxBooks'],
                'activeLoansCount' => $summary['activeLoansCount'],
            ],
        ]);
    }

    public function storeBook(LoanDraftBookRequest $request): RedirectResponse
    {
        session()->forget('loan_request_qr');
        $book = Book::query()->findOrFail($request->validatedBookId());

        $this->loanDraftService->addBook(
            $request->user(),
            $request->validatedBookId(),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Buku {$book->title} ditambahkan ke keranjang.",
        ]);

        return redirect()->back();
    }

    public function destroyBook(Request $request, Book $book): RedirectResponse
    {
        session()->forget('loan_request_qr');

        $this->loanDraftService->removeBook($request->user(), $book);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Buku {$book->title} dihapus dari keranjang.",
        ]);

        return redirect()->back();
    }

    public function generateQr(LoanDraftQrRequest $request): RedirectResponse
    {
        $checkout = $this->loanDraftService->generateQr(
            $request->user(),
            $request->validatedBookIds(),
        );

        session()->put('loan_request_qr', [
            'payload' => $checkout['payload'],
            'svg' => $checkout['qr_svg'],
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'QR berhasil dibuat.',
        ]);

        return redirect()->route('loans.request');
    }

    /**
     * @return array<string, mixed>
     */
    protected function toDraftPayload(LoanDraft $draft): array
    {
        $draft->loadMissing([
            'items.book.authors:id,name',
            'items.book.publisher:id,name',
        ]);

        // Pre-load available items count for all books in the draft to avoid N+1
        $draft->items->each(function (LoanDraftItem $item): void {
            $item->book->loadCount(['items as available_items_count' => fn ($query) => $query->available()]);
        });

        $qr = session('loan_request_qr');

        return [
            'id' => $draft->id,
            'status' => $draft->status,
            'itemsCount' => $draft->items->count(),
            'expiresAt' => AppTimezone::format($draft->expires_at, 'd F Y H:i'),
            'expiresAtIso' => $draft->expires_at?->toIso8601String(),
            'hasActiveQr' => $draft->hasActiveToken(),
            'qrCodeSvg' => $draft->hasActiveToken() && is_array($qr) ? ($qr['svg'] ?? null) : null,
            'selectedBookIds' => array_map('intval', $draft->selected_book_ids ?? []),
            'items' => $draft->items
                ->sortBy('id')
                ->values()
                ->map(fn (LoanDraftItem $item): array => [
                    'id' => $item->id,
                    'bookId' => $item->book_id,
                    'title' => $item->book->title,
                    'slug' => $item->book->slug,
                    'authors' => $item->book->authors->pluck('name')->values()->all(),
                    'isbn' => $item->book->isbn,
                    'issn' => $item->book->issn,
                    'availableItemsCount' => $item->book->available_items_count ?? 0,
                ])
                ->all(),
        ];
    }
}
