<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\User;
use App\Models\VisitLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KioskSyncController extends Controller
{
    /**
     * Verifikasi token otorisasi sinkronisasi edge.
     */
    protected function authorizeSync(Request $request): void
    {
        $expectedToken = (string) config('app.app.kiosk.sync_token');

        if (empty($expectedToken)) {
            abort(403, 'Sinkronisasi edge dinonaktifkan di server Cloud.');
        }

        $bearerToken = $request->bearerToken();

        if (! hash_equals($expectedToken, (string) $bearerToken)) {
            abort(401, 'Token sinkronisasi tidak valid.');
        }
    }

    /**
     * Endpoint Cloud: Mengirim data katalog dan user aktif ke Kiosk Edge.
     */
    public function getCatalog(Request $request): JsonResponse
    {
        $this->authorizeSync($request);

        $books = Book::query()
            ->with(['items:id,book_id,internal_code,shelf_location,status,condition'])
            ->where('is_published', true)
            ->get(['id', 'slug', 'title', 'subtitle', 'isbn', 'issn', 'ddc_code', 'published_year', 'is_published', 'is_borrowable'])
            ->map(fn (Book $b) => [
                'slug' => $b->slug,
                'title' => $b->title,
                'subtitle' => $b->subtitle,
                'isbn' => $b->isbn,
                'issn' => $b->issn,
                'ddc_code' => $b->ddc_code,
                'published_year' => $b->published_year,
                'is_published' => $b->is_published,
                'is_borrowable' => $b->is_borrowable,
                'items' => $b->items->map(fn (BookItem $item) => [
                    'internal_code' => $item->internal_code,
                    'shelf_location' => $item->shelf_location,
                    'status' => $item->status,
                    'condition' => $item->condition,
                ])->all(),
            ])
            ->all();

        $users = User::query()
            ->whereNotNull('email_verified_at')
            ->get(['name', 'email', 'phone_number', 'member_status', 'registered_at', 'email_verified_at'])
            ->all();

        return response()->json([
            'books' => $books,
            'users' => $users,
        ]);
    }

    /**
     * Endpoint Cloud: Menerima kumpulan transaksi dari Kiosk Edge.
     */
    public function storeTransactions(Request $request): JsonResponse
    {
        $this->authorizeSync($request);

        $visits = $request->input('visits', []);
        $loans = $request->input('loans', []);

        DB::transaction(function () use ($visits, $loans): void {
            // 1. Simpan Kunjungan
            foreach ($visits as $visit) {
                VisitLog::query()->firstOrCreate(
                    [
                        'name' => $visit['name'],
                        'identity_number' => $visit['identity_number'] ?? null,
                        'visited_at' => Carbon::parse($visit['visited_at']),
                    ],
                    [
                        'visitor_type' => $visit['visitor_type'] ?? 'student',
                        'institution' => $visit['institution'] ?? null,
                        'phone' => $visit['phone'] ?? null,
                        'purpose' => $visit['purpose'] ?? 'read',
                        'notes' => $visit['notes'] ?? null,
                    ]
                );
            }

            // 2. Simpan Transaksi Peminjaman & Pengembalian
            foreach ($loans as $loanData) {
                $user = User::query()->where('email', $loanData['user_email'])->first();

                if (! $user) {
                    continue;
                }

                $loan = Loan::query()->firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'borrowed_at' => Carbon::parse($loanData['borrowed_at']),
                    ],
                    [
                        'status' => $loanData['status'] ?? 'borrowed',
                        'due_at' => filled($loanData['due_at']) ? Carbon::parse($loanData['due_at']) : null,
                        'returned_at' => filled($loanData['returned_at']) ? Carbon::parse($loanData['returned_at']) : null,
                    ]
                );

                if (! empty($loanData['items']) && is_array($loanData['items'])) {
                    foreach ($loanData['items'] as $itemData) {
                        $bookItem = BookItem::query()
                            ->where('internal_code', $itemData['internal_code'])
                            ->first();

                        if (! $bookItem) {
                            continue;
                        }

                        $loanItem = LoanItem::query()->firstOrCreate(
                            [
                                'loan_id' => $loan->id,
                                'book_item_id' => $bookItem->id,
                            ],
                            [
                                'returned_at' => filled($itemData['returned_at']) ? Carbon::parse($itemData['returned_at']) : null,
                            ]
                        );

                        if ($itemData['returned_at'] && $loanItem->returned_at === null) {
                            $loanItem->update(['returned_at' => Carbon::parse($itemData['returned_at'])]);
                            $bookItem->update(['status' => 'available']);
                        }
                    }
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Transaksi edge berhasil disinkronkan ke Cloud.',
        ]);
    }
}
