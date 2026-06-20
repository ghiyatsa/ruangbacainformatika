<?php

use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

function createAdminUser(): User
{
    $user = User::factory()->create();
    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);
    $user->assignRole($role);

    return $user;
}

function createStaffUser(): User
{
    $user = User::factory()->create();
    $role = Role::firstOrCreate([
        'name' => 'staff',
        'guard_name' => 'web',
    ]);
    $user->assignRole($role);

    return $user;
}

it('redirects guests attempting to comment to login', function () {
    $post = Post::factory()->published()->create();

    post(route('blog.comments.store', $post->slug), [
        'content' => 'Ini adalah komentar tes.',
    ])->assertRedirect(route('login'));
});

it('allows authenticated users to write comments', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    actingAs($user)
        ->post(route('blog.comments.store', $post->slug), [
            'content' => 'Ini adalah komentar tes dari user.',
        ])
        ->assertRedirect();

    assertDatabaseHas('post_comments', [
        'post_id' => $post->id,
        'user_id' => $user->id,
        'content' => 'Ini adalah komentar tes dari user.',
        'parent_id' => null,
    ]);
});

it('allows authenticated users to reply to comments', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $post = Post::factory()->published()->create();

    $comment = PostComment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user1->id,
        'content' => 'Komentar pertama',
    ]);

    actingAs($user2)
        ->post(route('blog.comments.store', $post->slug), [
            'content' => 'Balasan dari user 2',
            'parent_id' => $comment->id,
        ])
        ->assertRedirect();

    assertDatabaseHas('post_comments', [
        'post_id' => $post->id,
        'user_id' => $user2->id,
        'content' => 'Balasan dari user 2',
        'parent_id' => $comment->id,
        'reply_to_comment_id' => $comment->id,
    ]);
});

it('stores reply-to-reply inside the same root thread', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    $post = Post::factory()->published()->create();

    $rootComment = PostComment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user1->id,
        'content' => 'Komentar utama',
    ]);

    $reply = PostComment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user2->id,
        'parent_id' => $rootComment->id,
        'reply_to_comment_id' => $rootComment->id,
        'content' => 'Balasan pertama',
    ]);

    actingAs($user3)
        ->post(route('blog.comments.store', $post->slug), [
            'content' => 'Balas balasan pertama',
            'parent_id' => $rootComment->id,
            'reply_to_comment_id' => $reply->id,
        ])
        ->assertRedirect();

    assertDatabaseHas('post_comments', [
        'post_id' => $post->id,
        'user_id' => $user3->id,
        'content' => 'Balas balasan pertama',
        'parent_id' => $rootComment->id,
        'reply_to_comment_id' => $reply->id,
    ]);
});

it('prevents users from deleting comments written by others', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $post = Post::factory()->published()->create();

    $comment = PostComment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user1->id,
        'content' => 'Komentar rahasia',
    ]);

    actingAs($user2)
        ->delete(route('blog.comments.destroy', $comment->id))
        ->assertForbidden();

    assertDatabaseHas('post_comments', [
        'id' => $comment->id,
    ]);
});

it('allows users to delete their own comments', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $comment = PostComment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user->id,
        'content' => 'Komentar saya sendiri',
    ]);

    actingAs($user)
        ->delete(route('blog.comments.destroy', $comment->id))
        ->assertRedirect();

    assertDatabaseMissing('post_comments', [
        'id' => $comment->id,
    ]);
});

it('allows administrative users to delete any comment', function () {
    $admin = createAdminUser();
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $comment = PostComment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user->id,
        'content' => 'Komentar user biasa',
    ]);

    actingAs($admin)
        ->delete(route('blog.comments.destroy', $comment->id))
        ->assertRedirect();

    assertDatabaseMissing('post_comments', [
        'id' => $comment->id,
    ]);
});

it('allows staff users to delete any comment', function () {
    $staff = createStaffUser();
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $comment = PostComment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user->id,
        'content' => 'Komentar user biasa',
    ]);

    actingAs($staff)
        ->delete(route('blog.comments.destroy', $comment->id))
        ->assertRedirect();

    assertDatabaseMissing('post_comments', [
        'id' => $comment->id,
    ]);
});

it('applies throttle middleware to the comment store route', function () {
    $route = route('blog.comments.store', 'sebuah-artikel');

    expect($route)->not->toBeNull();

    // Pastikan named limiter terdaftar dan terpasang di route.
    $middleware = app('router')->getRoutes()
        ->getByName('blog.comments.store')
        ->gatherMiddleware();

    expect($middleware)->toContain('throttle:blog-comments');
});

it('prevents commenting on draft posts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['status' => Post::STATUS_DRAFT]);

    actingAs($user)
        ->post(route('blog.comments.store', $post->slug), [
            'content' => 'Komentar pada draft.',
        ])
        ->assertNotFound();

    assertDatabaseMissing('post_comments', [
        'post_id' => $post->id,
    ]);
});

it('prevents commenting on pending posts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->pending()->create();

    actingAs($user)
        ->post(route('blog.comments.store', $post->slug), [
            'content' => 'Komentar pada post pending.',
        ])
        ->assertNotFound();
});

it('prevents commenting on rejected posts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['status' => Post::STATUS_REJECTED]);

    actingAs($user)
        ->post(route('blog.comments.store', $post->slug), [
            'content' => 'Komentar pada post ditolak.',
        ])
        ->assertNotFound();
});

it('rejects empty comment content', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    actingAs($user)
        ->post(route('blog.comments.store', $post->slug), [
            'content' => '   ',
        ])
        ->assertSessionHasErrors(['content']);
});

it('rejects comment content exceeding maximum length', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    actingAs($user)
        ->post(route('blog.comments.store', $post->slug), [
            'content' => str_repeat('a', 1001),
        ])
        ->assertSessionHasErrors(['content']);
});

it('shows the blog post with deferred comments', function () {
    $post = Post::factory()->published()->create();
    $comment = PostComment::factory()->create([
        'post_id' => $post->id,
        'content' => 'Komentar Utama',
    ]);
    $reply = PostComment::factory()->create([
        'post_id' => $post->id,
        'parent_id' => $comment->id,
        'reply_to_comment_id' => $comment->id,
        'content' => 'Balasan',
    ]);

    $response = get(route('blog.show', $post->slug));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('post.data.commentsCount')
        ->missing('post.data.comments')
        ->loadDeferredProps(fn ($reload) => $reload
            ->has('post.data.comments')
            ->where('post.data.comments.data.0.id', $comment->id)
            ->where('post.data.comments.data.0.replies.0.id', $reply->id)
        )
    );
});

it('prevents commenting when allow_comments is disabled', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create(['allow_comments' => false]);

    actingAs($user)
        ->post(route('blog.comments.store', $post->slug), [
            'content' => 'Komentar pada artikel dengan komentar dinonaktifkan.',
        ])
        ->assertStatus(403);

    assertDatabaseMissing('post_comments', [
        'post_id' => $post->id,
    ]);
});
