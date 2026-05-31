<?php

use App\Filament\Resources\StaticPages\Pages\CreateStaticPage;
use App\Filament\Resources\StaticPages\Pages\EditStaticPage;
use App\Models\ActivityLog;
use App\Models\StaticPage;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function makeStaticPageSuperAdmin(): User
{
    $user = User::factory()->create();

    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

it('super admin users can render the static pages resource', function () {
    $user = makeStaticPageSuperAdmin();

    actingAs($user)
        ->get('/admin/static-pages')
        ->assertOk()
        ->assertSee('Halaman Statis')
        ->assertSee('Tambah Halaman');

    actingAs($user)
        ->get('/admin/static-pages/create')
        ->assertOk()
        ->assertSee('Judul')
        ->assertSee('Slug')
        ->assertSee('Ringkasan halaman')
        ->assertSee('Dipakai pada alamat halaman publik.')
        ->assertSee('Isi halaman');
});

it('static pages can be created from the filament resource', function () {
    $user = makeStaticPageSuperAdmin();

    actingAs($user);

    Livewire::test(CreateStaticPage::class)
        ->fillForm([
            'title' => 'Panduan Layanan',
            'slug' => 'panduan-layanan',
            'summary' => 'Ringkasan panduan layanan.',
            'content' => '<h2>Panduan</h2><p>Isi panduan layanan.</p>',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $page = StaticPage::query()->where('slug', 'panduan-layanan')->first();

    expect($page)->not->toBeNull()
        ->and($page?->title)->toBe('Panduan Layanan')
        ->and($page?->page_key)->toBeNull();

    $log = ActivityLog::query()->latest('id')->first();

    expect($log?->action)->toBe('static_pages.created');
});

it('static pages can be updated from the filament resource', function () {
    $user = makeStaticPageSuperAdmin();
    $page = StaticPage::factory()->create([
        'title' => 'Panduan Layanan',
        'slug' => 'panduan-layanan',
    ]);

    actingAs($user);

    Livewire::test(EditStaticPage::class, ['record' => $page->getKey()])
        ->fillForm([
            'title' => 'Panduan Layanan Baru',
            'slug' => 'panduan-layanan-baru',
            'summary' => 'Ringkasan baru.',
            'content' => '<h2>Panduan Baru</h2><p>Isi baru.</p>',
            'is_active' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $page->refresh();

    expect($page->title)->toBe('Panduan Layanan Baru')
        ->and($page->slug)->toBe('panduan-layanan-baru');

    $log = ActivityLog::query()->latest('id')->first();

    expect($log?->action)->toBe('static_pages.updated');
});

it('custom static pages are available on the public route', function () {
    $page = StaticPage::factory()->create([
        'title' => 'FAQ Layanan',
        'slug' => 'faq-layanan',
        'summary' => 'Pertanyaan yang sering diajukan.',
        'content' => '<h2>FAQ</h2><p>Isi FAQ layanan.</p>',
        'page_key' => null,
        'is_active' => true,
    ]);

    get(route('pages.show', ['slug' => $page->slug]))
        ->assertOk()
        ->assertInertia(fn ($assert) => $assert
            ->component('static-page')
            ->where('title', 'FAQ Layanan')
            ->where('pageContent.summary', 'Pertanyaan yang sering diajukan.')
            ->where('pageContent.content', '<h2>FAQ</h2><p>Isi FAQ layanan.</p>'));
});
