<?php

use App\Filament\Resources\Books\Pages\CreateBook;
use App\Models\Book;
use App\Models\Publisher;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

function makeBookResourceSuperAdmin(): User
{
    $user = User::factory()->create();

    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

it('allows multiple journal entries with the same issn when edition and pages differ', function () {
    $user = makeBookResourceSuperAdmin();
    $publisher = Publisher::factory()->create(['name' => 'IT Press']);

    actingAs($user);

    Livewire::test(CreateBook::class)
        ->fillForm([
            'title' => 'Jurnal Informatika Vol. 1',
            'slug' => 'jurnal-informatika-vol-1',
            'issn' => '1234-5678',
            'edition' => 'Vol. 1',
            'pages' => '100-120',
            'language' => 'Indonesia',
            'publisher_id' => $publisher->getKey(),
            'is_published' => true,
            'is_borrowable' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    Livewire::test(CreateBook::class)
        ->fillForm([
            'title' => 'Jurnal Informatika Vol. 2',
            'slug' => 'jurnal-informatika-vol-2',
            'issn' => '1234-5678',
            'edition' => 'Vol. 2',
            'pages' => '121-145',
            'language' => 'Indonesia',
            'publisher_id' => $publisher->getKey(),
            'is_published' => true,
            'is_borrowable' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Book::query()->where('issn', '1234-5678')->count())->toBe(2);
});

it('rejects duplicate journal entries with the same issn edition and pages', function () {
    $user = makeBookResourceSuperAdmin();
    $publisher = Publisher::factory()->create(['name' => 'IT Press']);

    Book::factory()->create([
        'title' => 'Jurnal Informatika Vol. 1',
        'slug' => 'jurnal-informatika-vol-1',
        'isbn' => null,
        'issn' => '1234-5678',
        'edition' => 'Vol. 1',
        'pages' => '100-120',
        'publisher_id' => $publisher->getKey(),
    ]);

    actingAs($user);

    Livewire::test(CreateBook::class)
        ->fillForm([
            'title' => 'Jurnal Informatika Vol. 1 Salinan',
            'slug' => 'jurnal-informatika-vol-1-salinan',
            'issn' => '1234-5678',
            'edition' => 'Vol. 1',
            'pages' => '100-120',
            'language' => 'Indonesia',
            'publisher_id' => $publisher->getKey(),
            'is_published' => true,
            'is_borrowable' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['issn']);
});

it('does not require edition and pages for journal entries that rely on issn', function () {
    $user = makeBookResourceSuperAdmin();
    $publisher = Publisher::factory()->create(['name' => 'IT Press']);

    actingAs($user);

    Livewire::test(CreateBook::class)
        ->fillForm([
            'title' => 'Jurnal Informatika Tanpa Detail',
            'slug' => 'jurnal-informatika-tanpa-detail',
            'issn' => '1234-567X',
            'language' => 'Indonesia',
            'publisher_id' => $publisher->getKey(),
            'is_published' => true,
            'is_borrowable' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

it('validates issn format allowing X at the end', function () {
    $user = makeBookResourceSuperAdmin();
    $publisher = Publisher::factory()->create(['name' => 'IT Press']);

    actingAs($user);

    // Valid ISSN with X
    Livewire::test(CreateBook::class)
        ->fillForm([
            'title' => 'Jurnal Dengan X',
            'slug' => 'jurnal-dengan-x',
            'issn' => '1234-567X',
            'language' => 'Indonesia',
            'publisher_id' => $publisher->getKey(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Valid ISSN with lowercase x
    Livewire::test(CreateBook::class)
        ->fillForm([
            'title' => 'Jurnal Dengan x Kecil',
            'slug' => 'jurnal-dengan-x-kecil',
            'issn' => '1234-567x',
            'language' => 'Indonesia',
            'publisher_id' => $publisher->getKey(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Invalid ISSN (wrong characters)
    Livewire::test(CreateBook::class)
        ->fillForm([
            'title' => 'Jurnal Salah Karakter',
            'slug' => 'jurnal-salah-karakter',
            'issn' => '1234-567A',
            'language' => 'Indonesia',
            'publisher_id' => $publisher->getKey(),
        ])
        ->call('create')
        ->assertHasFormErrors(['issn']);
});

it('does not require edition and pages for isbn entries', function () {
    $user = makeBookResourceSuperAdmin();
    $publisher = Publisher::factory()->create(['name' => 'Gramedia']);

    actingAs($user);

    Livewire::test(CreateBook::class)
        ->fillForm([
            'title' => 'Buku ISBN Biasa',
            'slug' => 'buku-isbn-biasa',
            'isbn' => '9786020000001',
            'language' => 'Indonesia',
            'publisher_id' => $publisher->getKey(),
            'is_published' => true,
            'is_borrowable' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});
