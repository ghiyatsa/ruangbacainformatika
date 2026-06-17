<?php

use App\Filament\Dashboard\Resources\Posts\Pages\CreatePost;
use App\Filament\Dashboard\Resources\Posts\Pages\EditPost;
use App\Filament\Dashboard\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function makeMemberUser(): User
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

function makeStaffUser(): User
{
    $user = User::factory()->create([
        'email' => 'staff.'.rand(1000, 9999).'@unimal.ac.id',
        'is_approved' => true,
        'whatsapp_verified_at' => now(),
        'profile_completed_at' => now(),
    ]);

    $role = Role::firstOrCreate([
        'name' => 'staff',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

it('guests are redirected away from the member dashboard', function () {
    get('/dashboard')->assertRedirect(route('login'));
});

it('non member and non admin users can not access the dashboard', function () {
    $user = User::factory()->create([
        'email' => 'outsider@example.com',
        'is_approved' => false,
    ]);

    actingAs($user)
        ->get('/dashboard')
        ->assertForbidden();
});

it('member users can access the dashboard', function () {
    $user = makeMemberUser();

    actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});

it('member users can access the posts resource', function () {
    $user = makeMemberUser();

    actingAs($user)
        ->get('/dashboard/posts')
        ->assertOk()
        ->assertSee('Artikel')
        ->assertSee('Artikel Baru');
});

it('member users only see their own posts in the posts table', function () {
    $member1 = makeMemberUser();
    $member2 = makeMemberUser();

    $post1 = Post::factory()->create([
        'user_id' => $member1->id,
        'title' => 'Post by Member 1',
    ]);

    $post2 = Post::factory()->create([
        'user_id' => $member2->id,
        'title' => 'Post by Member 2',
    ]);

    actingAs($member1);

    Livewire::test(ListPosts::class)
        ->assertCanSeeTableRecords([$post1])
        ->assertCanNotSeeTableRecords([$post2]);
});

it('member users can create posts with pending review status', function () {
    $user = makeMemberUser();

    actingAs($user);

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'New Awesome Post',
            'slug' => 'new-awesome-post',
            'content' => '<p>Post content</p>',
            'status' => Post::STATUS_PENDING,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('posts', [
        'title' => 'New Awesome Post',
        'user_id' => $user->id,
        'status' => Post::STATUS_PENDING,
        'is_published' => false,
    ]);
});

it('member users can create draft posts', function () {
    $user = makeMemberUser();

    actingAs($user);

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Draft Post',
            'slug' => 'draft-post',
            'content' => '<p>Draft content</p>',
            'status' => Post::STATUS_DRAFT,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('posts', [
        'title' => 'Draft Post',
        'user_id' => $user->id,
        'status' => Post::STATUS_DRAFT,
        'is_published' => false,
    ]);
});

it('member users cannot force approved status while creating posts', function () {
    $user = makeMemberUser();

    actingAs($user);

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Tampered Post',
            'slug' => 'tampered-post',
            'content' => '<p>Injected content</p>',
            'status' => Post::STATUS_APPROVED,
        ])
        ->call('create')
        ->assertHasFormErrors(['status']);

    $this->assertDatabaseMissing('posts', [
        'title' => 'Tampered Post',
        'user_id' => $user->id,
    ]);
});

it('member users can edit their own posts', function () {
    $user = makeMemberUser();
    $post = Post::factory()->create([
        'user_id' => $user->id,
        'title' => 'Old Title',
    ]);

    actingAs($user);

    Livewire::test(EditPost::class, [
        'record' => $post->getRouteKey(),
    ])
        ->fillForm([
            'title' => 'Updated Title',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->fresh()->title)->toBe('Updated Title');
});

it('member users cannot edit other members posts because they are filtered out', function () {
    $member1 = makeMemberUser();
    $member2 = makeMemberUser();
    $post = Post::factory()->create([
        'user_id' => $member2->id,
        'title' => 'Post by Member 2',
    ]);

    actingAs($member1)
        ->get("/dashboard/posts/{$post->getRouteKey()}/edit")
        ->assertNotFound();
});

it('editing an approved post by member resubmits it for review', function () {
    $user = makeMemberUser();
    $reviewer = makeStaffUser();

    $post = Post::factory()->published()->create([
        'user_id' => $user->id,
        'reviewed_by_user_id' => $reviewer->id,
        'reviewed_at' => now()->subDay(),
        'published_at' => now()->subDay(),
        'title' => 'Already Published',
    ]);

    actingAs($user);

    Livewire::test(EditPost::class, [
        'record' => $post->getRouteKey(),
    ])
        ->fillForm([
            'title' => 'Already Published Revised',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $freshPost = $post->fresh();

    expect($freshPost->title)->toBe('Already Published Revised')
        ->and($freshPost->status)->toBe(Post::STATUS_PENDING)
        ->and($freshPost->is_published)->toBeFalse()
        ->and($freshPost->published_at)->toBeNull()
        ->and($freshPost->reviewed_by_user_id)->toBeNull()
        ->and($freshPost->reviewed_at)->toBeNull();
});

it('staff users can approve a post', function () {
    $staff = makeStaffUser();
    $member = makeMemberUser();
    $post = Post::factory()->pending()->create([
        'user_id' => $member->id,
        'title' => 'Pending Post',
    ]);

    actingAs($staff);

    Livewire::test(App\Filament\Resources\Posts\Pages\EditPost::class, [
        'record' => $post->getRouteKey(),
    ])
        ->fillForm([
            'status' => Post::STATUS_APPROVED,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $freshPost = $post->fresh();
    expect($freshPost->status)->toBe(Post::STATUS_APPROVED)
        ->and($freshPost->is_published)->toBeTrue()
        ->and($freshPost->reviewed_by_user_id)->toBe($staff->id)
        ->and($freshPost->reviewed_at)->not->toBeNull();
});

it('staff users can reject a post with a reason', function () {
    $staff = makeStaffUser();
    $member = makeMemberUser();
    $post = Post::factory()->pending()->create([
        'user_id' => $member->id,
        'title' => 'Pending Post',
    ]);

    actingAs($staff);

    Livewire::test(App\Filament\Resources\Posts\Pages\EditPost::class, [
        'record' => $post->getRouteKey(),
    ])
        ->fillForm([
            'status' => Post::STATUS_REJECTED,
            'rejection_reason' => 'Perbaiki tata bahasa.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $freshPost = $post->fresh();
    expect($freshPost->status)->toBe(Post::STATUS_REJECTED)
        ->and($freshPost->is_published)->toBeFalse()
        ->and($freshPost->reviewed_by_user_id)->toBe($staff->id)
        ->and($freshPost->reviewed_at)->not->toBeNull()
        ->and($freshPost->rejection_reason)->toBe('Perbaiki tata bahasa.');
});
