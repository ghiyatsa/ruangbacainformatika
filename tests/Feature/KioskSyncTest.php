<?php

use App\Models\Book;
use App\Models\BookItem;
use App\Models\User;
use App\Models\VisitLog;
use Illuminate\Support\Facades\Config;

beforeEach(function (): void {
    Config::set('app.app.kiosk.sync_token', 'test-edge-token-secret-12345');
});

test('sync endpoints reject request without valid bearer token', function (): void {
    $this->getJson(route('kiosk.sync.catalog'))
        ->assertStatus(401);

    $this->postJson(route('kiosk.sync.transactions'), [])
        ->assertStatus(401);
});

test('cloud can export catalog data for edge sync', function (): void {
    $book = Book::factory()->create(['is_published' => true]);
    BookItem::factory()->create([
        'book_id' => $book->id,
        'internal_code' => 'BC-SYNC-001',
    ]);
    User::factory()->create();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer test-edge-token-secret-12345',
    ])->getJson(route('kiosk.sync.catalog'));

    $response->assertOk()
        ->assertJsonStructure([
            'books' => [
                '*' => ['slug', 'title', 'items'],
            ],
            'users' => [
                '*' => ['name', 'email'],
            ],
        ])
        ->assertJsonPath('books.0.items.0.internal_code', 'BC-SYNC-001');
});

test('cloud can ingest offline visit logs from edge', function (): void {
    $visitPayload = [
        'visits' => [
            [
                'name' => 'Mahasiswa Offline',
                'visitor_type' => 'student',
                'identity_number' => '210170099',
                'purpose' => 'read',
                'visited_at' => now()->toIso8601String(),
            ],
        ],
        'loans' => [],
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer test-edge-token-secret-12345',
    ])->postJson(route('kiosk.sync.transactions'), $visitPayload);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas(VisitLog::class, [
        'name' => 'Mahasiswa Offline',
        'identity_number' => '210170099',
    ]);
});
