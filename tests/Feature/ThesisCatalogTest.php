<?php

use App\Models\Thesis;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'is_approved' => true,
        'profile_completed_at' => now(),
    ]);
    $user->assignRole('member');
    actingAs($user);
});

it('thesis catalog page renders results', function () {
    Thesis::factory()->create([
        'title' => 'Analisis Sistem Cerdas',
        'author_name' => 'Andi Pratama',
        'year' => 2024,
    ]);

    get(route('thesis.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('thesis/index')
            ->where('total', 1)
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('theses.data', 1)
                ->where('theses.data.0.title', 'Analisis Sistem Cerdas')
            ));
});

it('thesis catalog page filters by search keyword', function () {
    Thesis::factory()->create(['title' => 'Tesis Data Mining']);
    Thesis::factory()->create(['title' => 'Tesis Keamanan Jaringan']);

    get(route('thesis.index', ['search' => 'Mining']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('thesis/index')
            ->where('filters.search', 'Mining')
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('theses.data', 1)
                ->where('theses.data.0.title', 'Tesis Data Mining')
            ));
});

it('thesis catalog page returns the requested pagination page', function () {
    foreach (range(1, 21) as $number) {
        Thesis::factory()->create([
            'title' => sprintf('Tesis %02d', $number),
            'year' => 2024,
        ]);
    }

    get(route('thesis.index', ['page' => 2]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('thesis/index')
            ->where('total', 21)
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->where('theses.current_page', 2)
                ->where('theses.next_page_url', null)
                ->has('theses.data', 1)
                ->where('theses.data.0.title', 'Tesis 21')
            ));
});
