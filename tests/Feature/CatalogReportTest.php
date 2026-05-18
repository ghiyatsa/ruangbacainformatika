<?php

use App\Models\Book;
use App\Models\CatalogReport;
use App\Models\Thesis;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

test('guest can submit a catalog report for a published book', function () {
    $book = Book::factory()->published()->create([
        'title' => 'Clean Code',
        'isbn' => '9780132350884',
    ]);

    post(route('catalog-reports.store'), [
        'catalog_type' => CatalogReport::CATALOG_TYPE_BOOK,
        'catalog_id' => $book->id,
        'reporter_name' => 'Mahasiswa TI',
        'reporter_email' => 'mahasiswa@example.com',
        'message' => 'ISBN yang tampil tidak sesuai dengan data pada sampul buku.',
    ])
        ->assertRedirect()
        ->assertSessionHas('inertia.flash_data.toast.message', 'Laporan berhasil dikirim. Tim pengelola perpustakaan akan meninjau data ini.');

    assertDatabaseHas('catalog_reports', [
        'catalog_type' => CatalogReport::CATALOG_TYPE_BOOK,
        'reportable_type' => Book::class,
        'reportable_id' => $book->id,
        'catalog_title' => 'Clean Code',
        'catalog_url' => route('books.show', $book, absolute: false),
        'reporter_name' => 'Mahasiswa TI',
        'reporter_email' => 'mahasiswa@example.com',
        'status' => CatalogReport::STATUS_PENDING,
    ]);
});

test('authenticated user catalog report is linked to their account', function () {
    $user = User::factory()->create([
        'name' => 'Rina Pelapor',
        'email' => 'rina@example.com',
    ]);
    $thesis = Thesis::factory()->create([
        'title' => 'Analisis Data Akademik',
        'student_id' => '2201700001',
    ]);

    actingAs($user);

    post(route('catalog-reports.store'), [
        'catalog_type' => CatalogReport::CATALOG_TYPE_THESIS,
        'catalog_id' => $thesis->id,
        'message' => 'Tahun tesis ini perlu diperbarui karena belum sesuai data wisuda.',
    ])
        ->assertRedirect();

    assertDatabaseHas('catalog_reports', [
        'user_id' => $user->id,
        'catalog_type' => CatalogReport::CATALOG_TYPE_THESIS,
        'reportable_type' => Thesis::class,
        'reportable_id' => $thesis->id,
        'catalog_title' => 'Analisis Data Akademik',
        'catalog_url' => route('thesis.show', ['thesis' => '2201700001'], absolute: false),
        'reporter_name' => 'Rina Pelapor',
        'reporter_email' => 'rina@example.com',
    ]);
});

test('catalog report submission validates the message and catalog type', function () {
    post(route('catalog-reports.store'), [
        'catalog_type' => 'unknown',
        'catalog_id' => 999,
        'message' => 'pendek',
    ])
        ->assertSessionHasErrors([
            'catalog_type',
            'message',
        ]);
});
