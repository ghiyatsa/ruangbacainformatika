<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\User;
use App\Models\VisitLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KioskEdgeSyncService
{
    protected string $cloudUrl;

    protected string $syncToken;

    public function __construct()
    {
        $this->cloudUrl = rtrim((string) config('app.app.kiosk.cloud_url'), '/');
        $this->syncToken = (string) config('app.app.kiosk.sync_token');
    }

    /**
     * Pastikan instance edge memiliki konfigurasi valid.
     */
    public function isConfigured(): bool
    {
        return filled($this->cloudUrl) && filled($this->syncToken);
    }

    /**
     * Tarik katalog buku dan akun user aktif dari Cloud ke Edge.
     *
     * @return array{success: bool, message: string, books_synced?: int, users_synced?: int}
     */
    public function pullCatalogFromCloud(): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Konfigurasi KIOSK_CLOUD_URL atau KIOSK_SYNC_TOKEN belum diatur.',
            ];
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$this->syncToken,
                    'Accept' => 'application/json',
                ])
                ->get("{$this->cloudUrl}/api/kiosk/sync/catalog");

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Gagal terhubung ke Cloud: '.$response->status().' '.$response->body(),
                ];
            }

            $data = $response->json();
            $books = $data['books'] ?? [];
            $users = $data['users'] ?? [];

            DB::transaction(function () use ($books, $users): void {
                foreach ($users as $userData) {
                    User::query()->updateOrCreate(
                        ['email' => $userData['email']],
                        [
                            'name' => $userData['name'],
                            'phone_number' => $userData['phone_number'] ?? null,
                            'member_status' => $userData['member_status'] ?? 'active',
                            'email_verified_at' => $userData['email_verified_at'] ?? now(),
                            'registered_at' => $userData['registered_at'] ?? now(),
                            'updated_at' => now(),
                        ]
                    );
                }

                foreach ($books as $bookData) {
                    /** @var Book $book */
                    $book = Book::query()->updateOrCreate(
                        ['slug' => $bookData['slug']],
                        [
                            'title' => $bookData['title'],
                            'subtitle' => $bookData['subtitle'] ?? null,
                            'isbn' => $bookData['isbn'] ?? null,
                            'issn' => $bookData['issn'] ?? null,
                            'ddc_code' => $bookData['ddc_code'] ?? null,
                            'published_year' => $bookData['published_year'] ?? null,
                            'is_published' => $bookData['is_published'] ?? true,
                            'is_borrowable' => $bookData['is_borrowable'] ?? true,
                            'updated_at' => now(),
                        ]
                    );

                    if (! empty($bookData['items']) && is_array($bookData['items'])) {
                        foreach ($bookData['items'] as $itemData) {
                            BookItem::query()->updateOrCreate(
                                ['internal_code' => $itemData['internal_code']],
                                [
                                    'book_id' => $book->id,
                                    'shelf_location' => $itemData['shelf_location'] ?? null,
                                    'status' => $itemData['status'] ?? 'available',
                                    'condition' => $itemData['condition'] ?? 'good',
                                    'updated_at' => now(),
                                ]
                            );
                        }
                    }
                }
            });

            return [
                'success' => true,
                'message' => 'Katalog berhasil disinkronkan dari cloud.',
                'books_synced' => count($books),
                'users_synced' => count($users),
            ];
        } catch (\Throwable $e) {
            Log::error('Kiosk Edge Sync Pull Error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Exception saat pull katalog: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Unggah transaksi offline (Buku Tamu & Peminjaman) dari Edge ke Cloud.
     *
     * @return array{success: bool, message: string, visits_synced?: int, loans_synced?: int}
     */
    public function pushTransactionsToCloud(): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Konfigurasi KIOSK_CLOUD_URL atau KIOSK_SYNC_TOKEN belum diatur.',
            ];
        }

        // Ambil kunjungan & pinjaman dalam 7 hari terakhir
        $recentVisits = VisitLog::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->get()
            ->map(fn (VisitLog $v) => [
                'name' => $v->name,
                'visitor_type' => $v->visitor_type,
                'identity_number' => $v->identity_number,
                'institution' => $v->institution,
                'phone' => $v->phone,
                'purpose' => $v->purpose,
                'notes' => $v->notes,
                'visited_at' => $v->visited_at?->toIso8601String() ?? now()->toIso8601String(),
            ])
            ->all();

        $recentLoans = Loan::query()
            ->with(['user', 'loanItems.bookItem.book'])
            ->where('created_at', '>=', now()->subDays(7))
            ->get()
            ->map(function (Loan $l) {
                return [
                    'user_email' => $l->user?->email,
                    'status' => $l->status,
                    'borrowed_at' => $l->borrowed_at?->toIso8601String(),
                    'due_at' => $l->due_at?->toIso8601String(),
                    'returned_at' => $l->returned_at?->toIso8601String(),
                    'items' => $l->loanItems->map(fn (LoanItem $item) => [
                        'internal_code' => $item->bookItem?->internal_code,
                        'returned_at' => $item->returned_at?->toIso8601String(),
                    ])->filter(fn ($i) => filled($i['internal_code']))->values()->all(),
                ];
            })
            ->filter(fn ($l) => filled($l['user_email']))
            ->values()
            ->all();

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$this->syncToken,
                    'Accept' => 'application/json',
                ])
                ->post("{$this->cloudUrl}/api/kiosk/sync/transactions", [
                    'visits' => $recentVisits,
                    'loans' => $recentLoans,
                ]);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Gagal mengirim ke Cloud: '.$response->status().' '.$response->body(),
                ];
            }

            return [
                'success' => true,
                'message' => 'Transaksi offline berhasil diunggah ke Cloud.',
                'visits_synced' => count($recentVisits),
                'loans_synced' => count($recentLoans),
            ];
        } catch (\Throwable $e) {
            Log::error('Kiosk Edge Sync Push Error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Exception saat push transaksi: '.$e->getMessage(),
            ];
        }
    }
}
