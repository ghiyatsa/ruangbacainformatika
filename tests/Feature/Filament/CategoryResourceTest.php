<?php

use App\Filament\Resources\Categories\Pages\ViewCategory;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

function makeCategoryResourceSuperAdmin(): User
{
    $user = User::factory()->create();

    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

it('super admin users can access the category view page and see the books relation manager', function () {
    $user = makeCategoryResourceSuperAdmin();
    $category = Category::factory()->create([
        'name' => 'Pemrograman Web',
        'description' => 'Buku-buku tentang web development.',
    ]);

    $book = Book::factory()->create([
        'title' => 'Modern JavaScript',
    ]);

    $category->books()->attach($book);

    actingAs($user);

    Livewire::test(ViewCategory::class, [
        'record' => $category->getKey(),
    ])
        ->assertSuccessful()
        ->assertSee('Pemrograman Web')
        ->assertSee('Buku-buku tentang web development.')
        ->assertSee('Daftar Buku dalam Kategori')
        ->assertSee('Modern JavaScript');
});
