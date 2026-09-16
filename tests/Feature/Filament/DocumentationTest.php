<?php

use App\Filament\Resources\Docs\DocumentationResource;
use App\Filament\Resources\Docs\Pages\CreateDocumentation;
use App\Filament\Resources\Docs\Pages\EditDocumentation;
use App\Filament\Resources\Docs\Pages\ListDocumentations;
use App\Models\Documentation;
use App\Models\DocumentationCategory;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

function makeDocsAdmin(): User
{
    $user = User::factory()->create();

    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

beforeEach(function () {
    withoutMiddleware(PreventRequestForgery::class);
});

it('lists documentation pages for admins', function () {
    $category = DocumentationCategory::factory()->create(['name' => 'Memulai']);
    Documentation::factory()->create([
        'documentation_category_id' => $category->id,
        'title' => 'Panduan Peminjaman Buku',
    ]);

    actingAs(makeDocsAdmin())
        ->get(DocumentationResource::getUrl('index'))
        ->assertOk()
        ->assertSee('Panduan Peminjaman Buku');
});

it('creates a documentation page and records the editor', function () {
    $admin = makeDocsAdmin();
    $category = DocumentationCategory::factory()->create();

    actingAs($admin);

    Livewire::test(CreateDocumentation::class)
        ->fillForm([
            'documentation_category_id' => $category->id,
            'title' => 'Panduan Pengembalian Buku',
            'summary' => 'Ringkasan singkat.',
            'content' => '<p>Isi panduan.</p>',
            'is_published' => true,
            'sort_order' => 3,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $record = Documentation::query()->where('title', 'Panduan Pengembalian Buku')->first();

    expect($record)->not->toBeNull();
    expect($record->slug)->toBe('panduan-pengembalian-buku');
    expect($record->updated_by)->toBe($admin->id);
});

it('updates a documentation page and records the editor', function () {
    $admin = makeDocsAdmin();
    $record = Documentation::factory()->create(['title' => 'Judul Lama']);

    actingAs($admin);

    Livewire::test(EditDocumentation::class, ['record' => $record->getKey()])
        ->fillForm([
            'title' => 'Judul Baru',
            'content' => '<p>Isi baru.</p>',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $record->refresh();

    expect($record->title)->toBe('Judul Baru');
    expect($record->updated_by)->toBe($admin->id);
});

it('generates a unique slug when titles collide', function () {
    $category = DocumentationCategory::factory()->create();

    Documentation::factory()->create([
        'documentation_category_id' => $category->id,
        'title' => 'Panduan Sama',
    ]);

    $second = Documentation::factory()->create([
        'documentation_category_id' => $category->id,
        'title' => 'Panduan Sama',
    ]);

    expect($second->slug)->toBe('panduan-sama-2');
});

it('hides draft documentation from the published scope', function () {
    Documentation::factory()->create(['is_published' => true]);
    Documentation::factory()->draft()->create();

    expect(Documentation::query()->count())->toBe(2);
    expect(Documentation::published()->count())->toBe(1);
});

it('orders documentation pages by category sort order', function () {
    $later = DocumentationCategory::factory()->create(['name' => 'Kedua', 'sort_order' => 2]);
    $first = DocumentationCategory::factory()->create(['name' => 'Pertama', 'sort_order' => 1]);

    Documentation::factory()->create(['documentation_category_id' => $later->id]);
    Documentation::factory()->create(['documentation_category_id' => $first->id]);

    $ordered = DocumentationCategory::query()->orderBy('sort_order')->pluck('name')->all();

    expect($ordered)->toBe(['Pertama', 'Kedua']);
});

it('shows the draft badge count on navigation', function () {
    Documentation::factory()->draft()->count(3)->create();

    expect(DocumentationResource::getNavigationBadge())->toBe('3');
});

it('returns no badge when every documentation page is published', function () {
    Documentation::factory()->count(2)->create(['is_published' => true]);

    expect(DocumentationResource::getNavigationBadge())->toBeNull();
});

it('keeps the documentation resource inside the admin panel', function () {
    expect(DocumentationResource::getNavigationGroup())->toBe('Sistem');
    expect(DocumentationResource::getNavigationLabel())->toBe('Panduan Operasional');
});

it('reflects the documentation resource in the admin panel resource list', function () {
    actingAs(makeDocsAdmin());

    $resources = Filament\Facades\Filament::getPanel('admin')->getResources();

    expect($resources)->toContain(DocumentationResource::class);
});

it('renders the list page header action for admins', function () {
    actingAs(makeDocsAdmin())
        ->get(DocumentationResource::getUrl('index'))
        ->assertOk();
});

it('blocks guests from the documentation pages', function () {
    actingAs(User::factory()->create())
        ->get(DocumentationResource::getUrl('index'))
        ->assertForbidden();
});

it('exposes list, create, and edit pages on the resource', function () {
    expect(array_keys(DocumentationResource::getPages()))
        ->toBe(['index', 'create', 'edit']);

    expect(ListDocumentations::class)->toBeString();
});
