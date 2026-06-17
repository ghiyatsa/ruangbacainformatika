<?php

use App\Filament\Dashboard\Resources\Posts\Pages\CreatePost as MemberCreatePost;
use App\Filament\Dashboard\Resources\Posts\Pages\EditPost as MemberEditPost;
use App\Filament\Resources\Posts\Pages\EditPost as AdminEditPost;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function createTestMember(): User
{
    $user = User::factory()->create([
        'email' => 'student.'.rand(1000, 9999).'@mhs.unimal.ac.id',
        'is_approved' => true,
        'whatsapp_verified_at' => now(),
        'profile_completed_at' => now(),
    ]);

    $role = Role::firstOrCreate([
        'name' => 'member',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

function createTestAdmin(): User
{
    $user = User::factory()->create();

    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

it('member can render the create post page with correct titles and labels', function () {
    $user = createTestMember();

    actingAs($user)
        ->get('/dashboard/posts/create')
        ->assertOk()
        ->assertSee('Tulis Artikel Baru')
        ->assertSee('Foto Sampul Artikel')
        ->assertSee('Isi & Redaksi')
        ->assertSee('Judul Artikel')
        ->assertSee('Slug URL')
        ->assertSee('Abstrak / Ringkasan')
        ->assertSee('Badan Artikel')
        ->assertSee('Klasifikasi')
        ->assertSee('Penulis')
        ->assertSee($user->name)
        ->assertSee('Penerbitan')
        ->assertSee('Simpan Draf');
});

it('member create page has dynamic button labels based on status', function () {
    $user = createTestMember();
    actingAs($user);

    Livewire::test(MemberCreatePost::class)
        ->assertFormSet(['status' => Post::STATUS_DRAFT])
        ->assertSee('Simpan Draf')
        ->set('data.status', Post::STATUS_PENDING)
        ->assertSee('Ajukan Artikel');
});

it('member edit page has dynamic button labels based on status', function () {
    $user = createTestMember();
    $post = Post::factory()->create([
        'user_id' => $user->id,
        'status' => Post::STATUS_DRAFT,
    ]);

    actingAs($user);

    Livewire::test(MemberEditPost::class, [
        'record' => $post->getKey(),
    ])
        ->assertFormSet(['status' => Post::STATUS_DRAFT])
        ->assertSee('Simpan Draf')
        ->set('data.status', Post::STATUS_PENDING)
        ->assertSee('Ajukan Artikel');
});

it('member edit page shows review notes for rejected posts', function () {
    $user = createTestMember();
    $post = Post::factory()->create([
        'user_id' => $user->id,
        'status' => Post::STATUS_REJECTED,
        'rejection_reason' => 'Rapikan pembuka dan tambah sumber rujukan.',
    ]);

    actingAs($user)
        ->get("/dashboard/posts/{$post->getKey()}/edit")
        ->assertOk()
        ->assertSee('Catatan Review Sebelumnya')
        ->assertSee('Rapikan pembuka dan tambah sumber rujukan.');
});

it('member can only attach existing categories and tags', function () {
    $user = createTestMember();
    $category = PostCategory::factory()->create();
    $tag = PostTag::factory()->create();

    actingAs($user);

    Livewire::test(MemberCreatePost::class)
        ->fillForm([
            'title' => 'Artikel Member Valid',
            'slug' => 'artikel-member-valid',
            'summary' => 'Ringkasan singkat.',
            'content' => '<p>Isi artikel valid.</p>',
            'categories' => [$category->getKey()],
            'tags' => [$tag->getKey()],
            'status' => Post::STATUS_PENDING,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $post = Post::query()->where('slug', 'artikel-member-valid')->firstOrFail();

    expect($post->categories()->pluck('post_categories.id')->all())->toBe([$category->getKey()])
        ->and($post->tags()->pluck('post_tags.id')->all())->toBe([$tag->getKey()]);
});

it('member cannot submit unknown categories and tags', function () {
    $user = createTestMember();

    actingAs($user);

    Livewire::test(MemberCreatePost::class)
        ->fillForm([
            'title' => 'Artikel Member Invalid',
            'slug' => 'artikel-member-invalid',
            'summary' => 'Ringkasan singkat.',
            'content' => '<p>Isi artikel valid.</p>',
            'categories' => [999999],
            'tags' => [999999],
            'status' => Post::STATUS_PENDING,
        ])
        ->call('create')
        ->assertHasFormErrors(['categories.0', 'tags.0']);
});

it('admin create post page has correct title and labels', function () {
    $admin = createTestAdmin();

    actingAs($admin)
        ->get('/admin/posts/create')
        ->assertOk()
        ->assertSee('Tambah Artikel Baru')
        ->assertSee('Terbitkan Artikel')
        ->assertDontSee('Tinjau Artikel Member');
});

it('admin editing own post has correct title, labels, and fields are editable', function () {
    $admin = createTestAdmin();
    $post = Post::factory()->create([
        'user_id' => $admin->id,
        'title' => 'Admin Own Post Title',
        'status' => Post::STATUS_DRAFT,
    ]);

    actingAs($admin);

    $response = get("/admin/posts/{$post->getKey()}/edit")
        ->assertOk()
        ->assertSee('Ubah Artikel')
        ->assertSee('Terbitkan Artikel');

    // Confirm field is NOT disabled
    $content = $response->getContent();
    expect($content)->not()->toMatch('/id="form\\.title"[^>]*disabled/');
});

it('admin reviewing member post has correct title, labels, and fields are disabled', function () {
    $admin = createTestAdmin();
    $member = createTestMember();
    $post = Post::factory()->create([
        'user_id' => $member->id,
        'title' => 'Member Post Title',
        'status' => Post::STATUS_PENDING,
    ]);

    actingAs($admin);

    $response = get("/admin/posts/{$post->getKey()}/edit")
        ->assertOk()
        ->assertSee('Tinjau Artikel Member')
        ->assertSee('Simpan Keputusan');

    // Confirm that the title field is disabled
    $content = $response->getContent();
    expect($content)->toMatch('/id="form\\.title"[^>]*disabled|disabled[^>]*id="form\\.title"/');
});

it('admin post form exhibits conditional visibility and requirements for rejection reason', function () {
    $admin = createTestAdmin();
    $member = createTestMember();
    $post = Post::factory()->create([
        'user_id' => $member->id,
        'title' => 'Member Post Title',
        'status' => Post::STATUS_PENDING,
    ]);

    actingAs($admin);

    Livewire::test(AdminEditPost::class, [
        'record' => $post->getKey(),
    ])
        ->assertFormSet(['status' => Post::STATUS_PENDING])
        ->assertDontSee('Catatan Peninjau')
        ->set('data.status', Post::STATUS_REJECTED)
        ->assertSee('Catatan Peninjau')
        // Try to save with empty rejection reason
        ->fillForm(['rejection_reason' => ''])
        ->call('save')
        ->assertHasFormErrors(['rejection_reason'])
        // Fill rejection reason and successfully save
        ->fillForm(['rejection_reason' => 'Perlu revisi paragraf 2.'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->fresh()->status)->toBe(Post::STATUS_REJECTED)
        ->and($post->fresh()->rejection_reason)->toBe('Perlu revisi paragraf 2.');
});
