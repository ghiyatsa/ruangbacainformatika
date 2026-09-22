<?php

use App\Filament\Dashboard\Resources\Posts\Pages\EditPost as MemberEditPost;
use App\Filament\Resources\Posts\Pages\EditPost as AdminEditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostRevision;
use App\Models\PostTag;
use App\Models\User;
use App\Notifications\PostRevisionApprovedNotification;
use App\Notifications\PostRevisionRejectedNotification;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function makeRevisionMember(): User
{
    $role = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $user = User::factory()->create([
        'email' => 'revision.'.rand(1000, 9999).'@mhs.unimal.ac.id',
        'is_approved' => true,
        'whatsapp_verified_at' => now(),
        'profile_completed_at' => now(),
    ]);

    $user->assignRole($role);

    return $user;
}

function makeRevisionAdmin(): User
{
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('keeps an approved article published while the member edit awaits review', function () {
    $member = makeRevisionMember();
    $post = Post::factory()->published()->create([
        'user_id' => $member->id,
        'title' => 'Judul Lama',
        'content' => '<p>Isi lama</p>',
    ]);

    actingAs($member);

    Livewire::test(MemberEditPost::class, ['record' => $post->getKey()])
        ->fillForm([
            'title' => 'Judul Baru',
            'content' => '<p>Isi baru</p>',
            'status' => Post::STATUS_PENDING,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $post->refresh();

    // Artikel yang tayang tidak berubah dan tetap terbit.
    expect($post->status)->toBe(Post::STATUS_APPROVED)
        ->and($post->is_published)->toBeTrue()
        ->and($post->title)->toBe('Judul Lama')
        ->and($post->content)->toBe('<p>Isi lama</p>');

    // Perubahan disimpan sebagai revisi yang menunggu tinjauan.
    $revision = PostRevision::query()->where('post_id', $post->id)->firstOrFail();

    expect($revision->status)->toBe(PostRevision::STATUS_PENDING)
        ->and($revision->title)->toBe('Judul Baru')
        ->and($revision->content)->toBe('<p>Isi baru</p>');
});

it('still serves the old article body publicly while a revision is pending', function () {
    $member = makeRevisionMember();
    $post = Post::factory()->published()->create([
        'user_id' => $member->id,
        'title' => 'Versi Tayang',
        'content' => '<p>Badan versi tayang</p>',
    ]);

    actingAs($member);

    Livewire::test(MemberEditPost::class, ['record' => $post->getKey()])
        ->fillForm([
            'title' => 'Versi Draf',
            'content' => '<p>Badan versi draf</p>',
            'status' => Post::STATUS_PENDING,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    get(route('posts.show', $post->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('posts/show')
            ->where('post.data.title', 'Versi Tayang')
            ->where('post.data.content', fn (string $content): bool => str_contains($content, 'Badan versi tayang')));
});

it('does not create a revision when an approved article is saved without changes', function () {
    $member = makeRevisionMember();
    $post = Post::factory()->published()->create([
        'user_id' => $member->id,
        'content' => '<p>Isi yang tidak diubah</p>',
        'summary' => null,
        'cover_image' => null,
    ]);

    actingAs($member);

    Livewire::test(MemberEditPost::class, ['record' => $post->getKey()])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(PostRevision::query()->where('post_id', $post->id)->count())->toBe(0)
        ->and($post->fresh()->status)->toBe(Post::STATUS_APPROVED);
});

it('stages category and tag changes on the revision instead of the live article', function () {
    $member = makeRevisionMember();
    $oldCategory = PostCategory::factory()->create(['name' => 'Kategori Lama']);
    $newCategory = PostCategory::factory()->create(['name' => 'Kategori Baru']);
    $oldTag = PostTag::factory()->create(['name' => 'Tag Lama']);

    $post = Post::factory()->published()->create(['user_id' => $member->id]);
    $post->categories()->sync([$oldCategory->getKey()]);
    $post->tags()->sync([$oldTag->getKey()]);

    actingAs($member);

    Livewire::test(MemberEditPost::class, ['record' => $post->getKey()])
        ->fillForm([
            'categories' => [$newCategory->getKey()],
            'tags' => ['Tag Baru'],
            'status' => Post::STATUS_PENDING,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    // Artikel tayang tetap memakai kategori & tag lama.
    expect($post->fresh()->categories()->pluck('post_categories.id')->all())->toBe([$oldCategory->getKey()])
        ->and($post->fresh()->tags()->pluck('post_tags.name')->all())->toBe([$oldTag->name]);

    $revision = PostRevision::query()->where('post_id', $post->id)->firstOrFail();

    expect($revision->categories)->toBe([$newCategory->getKey()])
        ->and($revision->tags)->toBe(['Tag Baru']);
});

it('applies the revision to the article when the reviewer approves it', function () {
    Notification::fake();

    $member = makeRevisionMember();
    $admin = makeRevisionAdmin();
    $category = PostCategory::factory()->create();

    $post = Post::factory()->published()->create([
        'user_id' => $member->id,
        'title' => 'Judul Lama',
        'content' => '<p>Isi lama</p>',
    ]);

    $revision = PostRevision::factory()->create([
        'post_id' => $post->id,
        'user_id' => $member->id,
        'status' => PostRevision::STATUS_PENDING,
        'title' => 'Judul Baru',
        'slug' => $post->slug,
        'content' => '<p>Isi baru</p>',
        'categories' => [$category->getKey()],
        'tags' => ['Tag Revisi'],
    ]);

    actingAs($admin);

    Livewire::test(AdminEditPost::class, ['record' => $post->getKey()])
        ->fillForm(['status' => Post::STATUS_APPROVED])
        ->call('save')
        ->assertHasNoFormErrors();

    $post->refresh();

    expect($post->title)->toBe('Judul Baru')
        ->and($post->content)->toBe('<p>Isi baru</p>')
        ->and($post->status)->toBe(Post::STATUS_APPROVED)
        ->and($post->is_published)->toBeTrue()
        ->and($revision->fresh()->status)->toBe(PostRevision::STATUS_APPROVED)
        ->and($post->categories()->pluck('post_categories.id')->all())->toBe([$category->getKey()])
        ->and($post->tags()->pluck('post_tags.name')->all())->toBe(['Tag Revisi']);

    Notification::assertSentTo($member, PostRevisionApprovedNotification::class);
});

it('keeps the published article intact when the reviewer returns the revision', function () {
    Notification::fake();

    $member = makeRevisionMember();
    $admin = makeRevisionAdmin();

    $post = Post::factory()->published()->create([
        'user_id' => $member->id,
        'title' => 'Judul Tayang',
        'content' => '<p>Isi tayang</p>',
    ]);

    $revision = PostRevision::factory()->create([
        'post_id' => $post->id,
        'user_id' => $member->id,
        'status' => PostRevision::STATUS_PENDING,
        'title' => 'Judul Draf',
        'slug' => $post->slug,
        'content' => '<p>Isi draf</p>',
    ]);

    actingAs($admin);

    Livewire::test(AdminEditPost::class, ['record' => $post->getKey()])
        ->fillForm([
            'status' => Post::STATUS_REJECTED,
            'rejection_reason' => 'Perbaiki paragraf kedua.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $post->refresh();

    // Artikel tetap tayang dengan versi lama.
    expect($post->title)->toBe('Judul Tayang')
        ->and($post->content)->toBe('<p>Isi tayang</p>')
        ->and($post->status)->toBe(Post::STATUS_APPROVED);

    expect($revision->fresh()->status)->toBe(PostRevision::STATUS_REJECTED)
        ->and($revision->fresh()->rejection_reason)->toBe('Perbaiki paragraf kedua.');

    Notification::assertSentTo($member, PostRevisionRejectedNotification::class);
});

it('shows the revision rejection note to the member in the editor', function () {
    $member = makeRevisionMember();

    $post = Post::factory()->published()->create(['user_id' => $member->id]);

    PostRevision::factory()->create([
        'post_id' => $post->id,
        'user_id' => $member->id,
        'status' => PostRevision::STATUS_REJECTED,
        'slug' => $post->slug,
        'rejection_reason' => 'Tambah sumber rujukan.',
    ]);

    actingAs($member)
        ->get("/dashboard/posts/{$post->getKey()}/edit")
        ->assertOk()
        ->assertSee('Catatan Review Sebelumnya')
        ->assertSee('Tambah sumber rujukan.');
});

it('lets the reviewer approve a pending revision straight from the table action', function () {
    Notification::fake();

    $member = makeRevisionMember();
    $admin = makeRevisionAdmin();

    $post = Post::factory()->published()->create([
        'user_id' => $member->id,
        'title' => 'Judul Lama',
    ]);

    $revision = PostRevision::factory()->create([
        'post_id' => $post->id,
        'user_id' => $member->id,
        'status' => PostRevision::STATUS_PENDING,
        'title' => 'Judul Hasil Tinjauan',
        'slug' => $post->slug,
    ]);

    actingAs($admin);

    Livewire::test(ListPosts::class)
        ->callAction(
            TestAction::make('approve')->table($post),
        );

    expect($post->fresh()->title)->toBe('Judul Hasil Tinjauan')
        ->and($revision->fresh()->status)->toBe(PostRevision::STATUS_APPROVED);

    Notification::assertSentTo($member, PostRevisionApprovedNotification::class);
});

it('hydrates the member editor with the categories staged on the revision', function () {
    $member = makeRevisionMember();
    $liveCategory = PostCategory::factory()->create(['name' => 'Kategori Tayang']);
    $stagedCategory = PostCategory::factory()->create(['name' => 'Kategori Draf']);

    $post = Post::factory()->published()->create(['user_id' => $member->id]);
    $post->categories()->sync([$liveCategory->getKey()]);

    PostRevision::factory()->create([
        'post_id' => $post->id,
        'user_id' => $member->id,
        'status' => PostRevision::STATUS_PENDING,
        'slug' => $post->slug,
        'categories' => [$stagedCategory->getKey()],
    ]);

    actingAs($member);

    Livewire::test(MemberEditPost::class, ['record' => $post->getKey()])
        ->assertFormSet(['categories' => [$stagedCategory->getKey()]]);
});

it('lets a member keep editing a returned revision without unpublishing the article', function () {
    $member = makeRevisionMember();

    $post = Post::factory()->published()->create([
        'user_id' => $member->id,
        'title' => 'Judul Tayang',
        'content' => '<p>Isi tayang</p>',
    ]);

    $revision = PostRevision::factory()->create([
        'post_id' => $post->id,
        'user_id' => $member->id,
        'status' => PostRevision::STATUS_REJECTED,
        'title' => 'Judul Draf',
        'slug' => $post->slug,
        'content' => '<p>Isi draf</p>',
        'rejection_reason' => 'Perbaiki pembuka.',
    ]);

    actingAs($member);

    // Editor menampilkan draf revisi, bukan artikel tayang.
    Livewire::test(MemberEditPost::class, ['record' => $post->getKey()])
        ->assertFormSet(['title' => 'Judul Draf'])
        ->fillForm([
            'title' => 'Judul Draf Revisi',
            'content' => '<p>Isi draf revisi</p>',
            'status' => Post::STATUS_PENDING,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    // Masih revisi yang sama, tidak menumpuk revisi baru.
    expect(PostRevision::query()->where('post_id', $post->id)->count())->toBe(1);

    $revision->refresh();

    expect($revision->title)->toBe('Judul Draf Revisi')
        ->and($revision->status)->toBe(PostRevision::STATUS_PENDING)
        ->and($revision->rejection_reason)->toBeNull();

    // Artikel tayang tetap utuh.
    expect($post->fresh()->title)->toBe('Judul Tayang')
        ->and($post->fresh()->status)->toBe(Post::STATUS_APPROVED);
});
