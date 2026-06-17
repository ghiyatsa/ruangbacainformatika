<?php

use App\Filament\Resources\Authors\Pages\ViewAuthor;
use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

function makeAuthorResourceSuperAdmin(): User
{
    $user = User::factory()->create();

    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

it('super admin users can access the author view page and see the books relation manager', function () {
    $user = makeAuthorResourceSuperAdmin();
    $author = Author::factory()->create([
        'name' => 'Robert C. Martin',
        'email' => 'unclebob@example.com',
        'bio' => 'Famous software engineer and author.',
    ]);

    $book = Book::factory()->create([
        'title' => 'Clean Code',
        'published_year' => 2008,
    ]);

    $author->books()->attach($book);

    actingAs($user);

    Livewire::test(ViewAuthor::class, [
        'record' => $author->getKey(),
    ])
        ->assertSuccessful()
        ->assertSee('Robert C. Martin')
        ->assertSee('unclebob@example.com')
        ->assertSee('Famous software engineer and author.')
        ->assertSee('Daftar Buku yang Ditulis')
        ->assertSee('Clean Code');
});
