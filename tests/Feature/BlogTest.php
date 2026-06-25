<?php

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Services\Blog\BlogQueryService;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

it('blog index only shows approved posts', function () {
    $approvedPost = Post::factory()->published()->create([
        'title' => 'Artikel Approved',
    ]);

    Post::factory()->create([
        'title' => 'Artikel Draft',
        'status' => Post::STATUS_DRAFT,
    ]);

    get(route('blog.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('blog/index')
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('posts.data', 1)
                ->where('posts.data.0.title', $approvedPost->title)
            ));
});

it('blog detail returns 404 for unpublished posts', function () {
    $post = Post::factory()->create([
        'status' => Post::STATUS_PENDING,
    ]);

    get(route('blog.show', $post->slug))
        ->assertNotFound();
});

it('blog detail increments view count for approved posts', function () {
    $post = Post::factory()->published()->create([
        'view_count' => 5,
    ]);

    get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('blog/show')
            ->where('post.data.title', $post->title));

    expect($post->fresh()->view_count)->toBe(6);
});

it('blog index can filter by category and tag', function () {
    $category = PostCategory::factory()->create([
        'name' => 'Teknologi',
    ]);
    $otherCategory = PostCategory::factory()->create([
        'name' => 'Komunitas',
    ]);
    $tag = PostTag::factory()->create([
        'name' => 'Laravel',
    ]);
    $otherTag = PostTag::factory()->create([
        'name' => 'Desain',
    ]);

    $matchingPost = Post::factory()->published()->create([
        'title' => 'Laravel untuk Komunitas',
    ]);
    $matchingPost->categories()->attach($category);
    $matchingPost->tags()->attach($tag);

    $nonMatchingPost = Post::factory()->published()->create([
        'title' => 'Artikel Lain',
    ]);
    $nonMatchingPost->categories()->attach($otherCategory);
    $nonMatchingPost->tags()->attach($otherTag);

    get(route('blog.index', [
        'category' => $category->slug,
        'tag' => $tag->slug,
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('blog/index')
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('posts.data', 1)
                ->where('posts.data.0.title', $matchingPost->title)
            ));
});

it('homepage shares latest approved posts', function () {
    $latestPost = Post::factory()->published()->create([
        'title' => 'Artikel Beranda',
        'published_at' => now(),
    ]);

    Post::factory()->create([
        'title' => 'Artikel Draft Beranda',
        'status' => Post::STATUS_DRAFT,
    ]);

    get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome/index')
            ->has('latestPosts', 1)
            ->where('latestPosts.0.title', $latestPost->title));
});

it('popular posts are ordered by view_count descending', function () {
    $postLowView = Post::factory()->published()->create([
        'title' => 'Low View',
        'view_count' => 10,
        'published_at' => now()->subDays(2),
    ]);

    $postHighView = Post::factory()->published()->create([
        'title' => 'High View',
        'view_count' => 100,
        'published_at' => now()->subDay(),
    ]);

    $service = app(BlogQueryService::class);
    $popular = $service->popularPosts(2);

    expect($popular)->toHaveCount(2)
        ->and($popular[0]->id)->toBe($postHighView->id)
        ->and($popular[1]->id)->toBe($postLowView->id);
});

it('allows anyone with the preview token to preview draft posts', function () {
    $post = Post::factory()->create([
        'status' => Post::STATUS_DRAFT,
    ]);

    get(route('blog.preview', $post->preview_token))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('blog/show')
            ->where('post.data.title', $post->title));
});

it('renders preview with cached data if available', function () {
    $post = Post::factory()->create([
        'title' => 'Original Title',
        'status' => Post::STATUS_DRAFT,
    ]);

    // Put some unsaved data in the cache
    Cache::put(
        'post_preview_'.$post->preview_token,
        [
            'title' => 'Cached Title',
            'content' => 'Cached content body...',
        ],
        now()->addHours(2)
    );

    get(route('blog.preview', $post->preview_token))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('blog/show')
            ->where('post.data.title', 'Cached Title')
            ->where('post.data.contentText', 'Cached content body...'));
});

it('converts Tiptap JSON content to HTML on preview', function () {
    $post = Post::factory()->create([
        'status' => Post::STATUS_DRAFT,
    ]);

    // Put some unsaved Tiptap JSON content in the cache
    Cache::put(
        'post_preview_'.$post->preview_token,
        [
            'title' => 'Tiptap Title',
            'content' => [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Hello from Tiptap!',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        now()->addHours(2)
    );

    get(route('blog.preview', $post->preview_token))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('blog/show')
            ->where('post.data.title', 'Tiptap Title')
            ->where('post.data.contentText', 'Hello from Tiptap!'));
});
