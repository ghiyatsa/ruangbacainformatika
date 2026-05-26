<?php

use App\Models\Book;
use App\Models\InternshipReport;
use App\Models\Publisher;
use App\Models\Skripsi;
use App\Models\Thesis;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\get;

beforeEach(function () {
    Queue::fake();
});

it('returns empty array when query is empty', function () {
    get(route('search', ['q' => '']))
        ->assertOk()
        ->assertExactJson([]);
});

it('returns empty array when query is whitespace', function () {
    get(route('search', ['q' => '   ']))
        ->assertOk()
        ->assertExactJson([]);
});

it('returns books matching search query', function () {
    $publisher = Publisher::factory()->create();

    Book::factory()->create([
        'title' => 'Pemrograman Laravel untuk Pemula',
        'is_published' => true,
        'publisher_id' => $publisher->id,
    ]);

    Book::factory()->create([
        'title' => 'Buku Tanpa Keterkaitan',
        'is_published' => true,
        'publisher_id' => $publisher->id,
    ]);

    $response = get(route('search', ['q' => 'Laravel']))
        ->assertOk()
        ->assertJsonStructure([
            'books',
            'skripsis',
            'internshipReports',
            'theses',
        ]);

    expect($response->json('books'))->toHaveCount(1)
        ->and($response->json('books.0.title'))->toBe('Pemrograman Laravel untuk Pemula');
});

it('returns only published books in search results', function () {
    $publisher = Publisher::factory()->create();

    Book::factory()->create([
        'title' => 'Buku Laravel Diterbitkan',
        'is_published' => true,
        'publisher_id' => $publisher->id,
    ]);

    Book::factory()->create([
        'title' => 'Buku Laravel Belum Diterbitkan',
        'is_published' => false,
        'publisher_id' => $publisher->id,
    ]);

    $response = get(route('search', ['q' => 'Laravel']))
        ->assertOk();

    expect($response->json('books'))->toHaveCount(1)
        ->and($response->json('books.0.title'))->toBe('Buku Laravel Diterbitkan');
});

it('returns skripsis matching search query', function () {
    Skripsi::factory()->create([
        'title' => 'Sistem Pakar Berbasis Laravel',
        'author_name' => 'Ahmad Fauzi',
    ]);

    Skripsi::factory()->create([
        'title' => 'Analisis Kecerdasan Buatan',
        'author_name' => 'Budi Santoso',
    ]);

    $response = get(route('search', ['q' => 'Laravel']))
        ->assertOk();

    expect($response->json('skripsis'))->toHaveCount(1)
        ->and($response->json('skripsis.0.title'))->toBe('Sistem Pakar Berbasis Laravel');
});

it('returns skripsis matching author name', function () {
    Skripsi::factory()->create([
        'title' => 'Judul Tidak Relevan',
        'author_name' => 'Ahmad Fauzi Laravel Expert',
    ]);

    $response = get(route('search', ['q' => 'Fauzi']))
        ->assertOk();

    expect($response->json('skripsis'))->toHaveCount(1);
});

it('returns internship reports matching search query', function () {
    InternshipReport::factory()->create([
        'title' => 'Laporan Kerja Praktik Laravel Development',
        'author_name' => 'Citra Dewi',
    ]);

    InternshipReport::factory()->create([
        'title' => 'Laporan Tidak Relevan',
        'author_name' => 'Dani Kurniawan',
    ]);

    $response = get(route('search', ['q' => 'Laravel']))
        ->assertOk();

    expect($response->json('internshipReports'))->toHaveCount(1)
        ->and($response->json('internshipReports.0.title'))->toBe('Laporan Kerja Praktik Laravel Development');
});

it('returns theses matching search query', function () {
    Thesis::factory()->create([
        'title' => 'Pengembangan Aplikasi Laravel untuk Enterprise',
        'author_name' => 'Eko Prasetyo',
    ]);

    Thesis::factory()->create([
        'title' => 'Tesis Tidak Relevan',
        'author_name' => 'Fitri Handayani',
    ]);

    $response = get(route('search', ['q' => 'Laravel']))
        ->assertOk();

    expect($response->json('theses'))->toHaveCount(1)
        ->and($response->json('theses.0.title'))->toBe('Pengembangan Aplikasi Laravel untuk Enterprise');
});

it('returns results across all content types simultaneously', function () {
    $publisher = Publisher::factory()->create();

    Book::factory()->create([
        'title' => 'Machine Learning Dasar',
        'is_published' => true,
        'publisher_id' => $publisher->id,
    ]);
    Skripsi::factory()->create(['title' => 'Implementasi Machine Learning']);
    InternshipReport::factory()->create(['title' => 'Machine Learning di Industri']);
    Thesis::factory()->create(['title' => 'Machine Learning Lanjutan']);

    $response = get(route('search', ['q' => 'Machine Learning']))
        ->assertOk();

    expect($response->json('books'))->toHaveCount(1)
        ->and($response->json('skripsis'))->toHaveCount(1)
        ->and($response->json('internshipReports'))->toHaveCount(1)
        ->and($response->json('theses'))->toHaveCount(1);
});
