<?php

use App\Filament\Resources\Publishers\Pages\ViewPublisher;
use App\Models\Book;
use App\Models\Publisher;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

function makePublisherResourceSuperAdmin(): User
{
    $user = User::factory()->create();

    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

it('super admin users can access the publisher view page and see the books relation manager', function () {
    $user = makePublisherResourceSuperAdmin();
    $publisher = Publisher::factory()->create([
        'name' => 'O\'Reilly Media',
        'city' => 'Sebastopol',
        'description' => 'A famous book publisher.',
    ]);

    $book = Book::factory()->create([
        'title' => 'Learning PHP',
        'publisher_id' => $publisher->getKey(),
    ]);

    actingAs($user);

    Livewire::test(ViewPublisher::class, [
        'record' => $publisher->getKey(),
    ])
        ->assertSuccessful()
        ->assertSee('O\'Reilly Media')
        ->assertSee('Sebastopol')
        ->assertSee('A famous book publisher.')
        ->assertSee('Daftar Buku yang Diterbitkan')
        ->assertSee('Learning PHP');
});
