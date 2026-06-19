<?php

namespace App\Http\Controllers;

use App\Http\Resources\BlogPostResource;
use App\Models\Post;
use App\Services\Blog\BlogQueryService;
use App\Support\PageMeta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function __construct(
        protected BlogQueryService $blogQueryService,
        protected PageMeta $pageMeta,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $this->blogQueryService->filtersFromRequest($request);

        return Inertia::render('blog/index', [
            'filters' => $filters,
            'activeFilterLabels' => $this->blogQueryService->activeFilterLabels($filters),
            'categories' => Inertia::defer(fn () => $this->blogQueryService->categories()),
            'tags' => Inertia::defer(fn () => $this->blogQueryService->tags()),
            'posts' => Inertia::defer(function () use ($filters) {
                $posts = $this->blogQueryService->paginatePosts($filters);
                $paginated = $posts->toArray();
                $paginated['data'] = BlogPostResource::collection($posts->getCollection())->resolve();
                return $paginated;
            }),
            'popularPosts' => Inertia::defer(fn () => BlogPostResource::collection($this->blogQueryService->popularPosts())->resolve()),
        ])->withViewData([
            'meta' => $this->pageMeta->forBlogIndex(),
        ]);
    }

    public function show(Post $post): Response
    {
        abort_unless($post->status === Post::STATUS_APPROVED, 404);

        $post->increment('view_count');

        $post->load([
            'user:id,name,avatar_url',
            'reviewedBy:id,name',
            'categories:id,name,slug',
            'tags:id,name,slug',
        ]);

        return Inertia::render('blog/show', [
            'post' => new BlogPostResource($post->fresh(['user:id,name,avatar_url', 'reviewedBy:id,name', 'categories:id,name,slug', 'tags:id,name,slug'])),
            'relatedPosts' => Inertia::defer(fn () => BlogPostResource::collection($this->blogQueryService->relatedPosts($post))->resolve()),
            'popularPosts' => Inertia::defer(fn () => BlogPostResource::collection($this->blogQueryService->popularPosts())->resolve()),
            'categories' => Inertia::defer(fn () => $this->blogQueryService->categories()),
            'tags' => Inertia::defer(fn () => $this->blogQueryService->tags()),
        ])->withViewData([
            'meta' => $this->pageMeta->forPost($post),
        ]);
    }
}
