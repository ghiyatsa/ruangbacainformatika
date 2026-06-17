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
        $posts = $this->blogQueryService->paginatePosts($filters);

        $paginated = $posts->toArray();
        $paginated['data'] = BlogPostResource::collection($posts->getCollection())->resolve();

        return Inertia::render('blog/index', [
            'filters' => $filters,
            'activeFilterLabels' => $this->blogQueryService->activeFilterLabels($filters),
            'categories' => $this->blogQueryService->categories(),
            'tags' => $this->blogQueryService->tags(),
            'posts' => $paginated,
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
            'relatedPosts' => BlogPostResource::collection($this->blogQueryService->relatedPosts($post))->resolve(),
        ])->withViewData([
            'meta' => $this->pageMeta->forPost($post),
        ]);
    }
}
