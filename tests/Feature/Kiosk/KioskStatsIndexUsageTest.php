<?php

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\User;
use App\Models\VisitLog;
use App\Services\Kiosk\KioskDashboardStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

/**
 * Endpoint GET /api/kiosk/bootstrap dipanggil kiosk setiap 15 detik
 * (~5.760 kali/hari/perangkat). Setiap panggilan menjalankan tiga COUNT di
 * KioskDashboardStatsService. Uji ini menjaga agar COUNT tersebut tetap
 * terlayani indeks dan tidak berubah menjadi pemindaian tabel penuh saat
 * data kunjungan/peminjaman bertambah.
 */
it('melayani COUNT statistik kiosk lewat indeks, bukan pemindaian tabel', function () {
    $member = User::factory()->create(['is_approved' => true]);
    actingAs($member);

    $now = now();

    $visits = [];
    for ($i = 0; $i < 2000; $i++) {
        $visits[] = [
            'name' => 'Pengunjung '.$i,
            'visited_at' => $now->copy()->subMinutes($i),
            'visitor_type' => 'umum',
            'purpose' => 'membaca',
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
    foreach (array_chunk($visits, 1000) as $chunk) {
        VisitLog::query()->insert($chunk);
    }

    $book = Book::factory()->create();
    $bookItem = BookItem::factory()->create(['book_id' => $book->id]);

    $loans = [];
    for ($i = 0; $i < 500; $i++) {
        $loans[] = [
            'user_id' => $member->id,
            'status' => 'active',
            'borrowed_at' => $now->copy()->subHours($i % 24),
            'due_at' => $now->copy()->addDays(7),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
    foreach (array_chunk($loans, 500) as $chunk) {
        Loan::query()->insert($chunk);
    }

    $items = [];
    for ($i = 0; $i < 500; $i++) {
        $items[] = [
            'loan_id' => ($i % 500) + 1,
            'book_item_id' => $bookItem->id,
            'returned_at' => $i % 3 === 0 ? $now->copy()->subHours($i % 24) : null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
    foreach (array_chunk($items, 500) as $chunk) {
        LoanItem::query()->insert($chunk);
    }

    [$dayStart, $dayEnd] = VisitLog::adminDayRange();

    // Bentuk query identik dengan KioskDashboardStatsService::getStatsForRequest().
    $plans = [
        'loans.borrowed_at' => [
            'select count(*) from loan_items inner join loans on loans.id = loan_items.loan_id where loans.borrowed_at between ? and ?',
            [$dayStart, $dayEnd],
        ],
        'loan_items.returned_at' => [
            'select count(*) from loan_items where returned_at between ? and ?',
            [$dayStart, $dayEnd],
        ],
    ];

    foreach ($plans as $column => $query) {
        $detail = implode(' ', array_map(
            fn ($row) => $row->detail ?? '',
            DB::select('EXPLAIN QUERY PLAN '.$query[0], $query[1]),
        ));

        expect($detail)
            ->not->toContain('SCAN loans')
            ->not->toContain('SCAN loan_items')
            ->and($detail)->toContain('USING COVERING INDEX');
    }

    // Sanity: service tetap mengembalikan angka hari ini.
    $stats = app(KioskDashboardStatsService::class)
        ->getStatsForRequest(Request::create('/api/kiosk/bootstrap', 'GET'), null);

    expect($stats['todayVisits'])->toBeGreaterThan(0);
});
