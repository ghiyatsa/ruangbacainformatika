<?php

use App\Models\Book;
use App\Models\Setting;
use App\Models\Skripsi;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\get;

it('renders the site open graph image with the configured logo-driven identity', function () {
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'site_name'],
        ['value' => 'Ruang Baca Custom'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'site_tagline'],
        ['value' => 'Portal koleksi dan layanan digital'],
    );

    get(route('og.site'))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/svg+xml')
        ->assertSee('Ruang Baca Custom', false)
        ->assertSee('Portal koleksi dan layanan digital', false)
        ->assertSee('viewBox="0 0 1200 600"', false);
});

it('renders a catalog detail open graph image with title author and logo', function () {
    $book = Book::factory()->create([
        'title' => 'Clean Code untuk Pengembangan Sistem Perpustakaan',
        'slug' => 'clean-code-perpustakaan',
    ]);
    $book->authors()->create(['name' => 'Robert Martin', 'slug' => 'robert-martin']);

    get(route('og.books.show', $book))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/svg+xml')
        ->assertSee('Katalog Buku', false)
        ->assertSee('Clean Code untuk', false)
        ->assertSee('Pengembangan Sistem', false)
        ->assertSee('Perpustakaan', false)
        ->assertSee('Robert Martin', false)
        ->assertSee('Ruang Baca Informatika', false);
});

it('renders an academic document open graph image without requiring a cover', function () {
    Queue::fake();

    $skripsi = Skripsi::factory()->create([
        'title' => 'Sistem Rekomendasi Perpustakaan Berbasis Topik',
        'author_name' => 'Nadia Putri',
        'student_id' => '2301700999',
    ]);

    get(route('og.skripsi.show', $skripsi))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/svg+xml')
        ->assertSee('Skripsi', false)
        ->assertSee('Sistem Rekomendasi', false)
        ->assertSee('Nadia Putri', false);
});
