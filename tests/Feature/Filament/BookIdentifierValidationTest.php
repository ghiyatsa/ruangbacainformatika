<?php

use App\Filament\Resources\Books\Pages\CreateBook;
use App\Filament\Resources\Books\Pages\EditBook;
use App\Models\Book;
use App\Models\Publisher;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

function bookTestAdmin(): User
{
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('allows creating a book with only isbn and an empty issn', function () {
    $publisher = Publisher::factory()->create();

    actingAs(bookTestAdmin());

    Livewire::test(CreateBook::class)
        ->fillForm([
            'title' => 'Buku ISBN Saja',
            'slug' => 'buku-isbn-saja',
            'publisher_id' => $publisher->id,
            'isbn' => '9786020000001',
        ])
        ->call('create')
        ->assertHasNoFormErrors(['isbn', 'issn']);

    assertDatabaseHas('books', [
        'title' => 'Buku ISBN Saja',
        'isbn' => '9786020000001',
    ]);
});

it('allows creating a book with only issn and an empty isbn', function () {
    $publisher = Publisher::factory()->create();

    actingAs(bookTestAdmin());

    Livewire::test(CreateBook::class)
        ->fillForm([
            'title' => 'Jurnal ISSN Saja',
            'slug' => 'jurnal-issn-saja',
            'publisher_id' => $publisher->id,
            'issn' => '1234-5678',
        ])
        ->call('create')
        ->assertHasNoFormErrors(['isbn', 'issn']);

    assertDatabaseHas('books', [
        'title' => 'Jurnal ISSN Saja',
        'issn' => '1234-5678',
    ]);
});

it('allows saving an existing isbn book without a valid issn error', function () {
    $publisher = Publisher::factory()->create();
    $book = Book::factory()->create([
        'title' => 'Buku ISBN Edit',
        'isbn' => '9786020000002',
        'issn' => null,
        'is_published' => true,
        'publisher_id' => $publisher->id,
    ]);

    actingAs(bookTestAdmin());

    Livewire::test(EditBook::class, ['record' => $book->id])
        ->fillForm([
            'title' => 'Buku ISBN Edit',
            'publisher_id' => $publisher->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors(['isbn', 'issn']);
});
