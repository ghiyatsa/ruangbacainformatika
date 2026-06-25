<?php

namespace App\Http\Controllers;

use App\Http\Resources\BlogPostResource;
use App\Http\Resources\PostCommentResource;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Services\Blog\BlogQueryService;
use App\Support\PageMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Tiptap\Editor;

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

        $postResource = new BlogPostResource(
            $post->fresh(['user:id,name,avatar_url', 'reviewedBy:id,name', 'categories:id,name,slug', 'tags:id,name,slug'])
        );

        $commentsPage = request()->integer('comments_page', 1);

        return Inertia::render('blog/show', [
            'isPreview' => false,
            'post' => [
                'data' => array_merge(
                    $postResource->resolve(),
                    [
                        'commentsCount' => $post->comments()->count(),
                        'comments' => Inertia::defer(function () use ($post, $commentsPage) {
                            $commentsQuery = $post->comments()
                                ->whereNull('parent_id')
                                ->with(['user', 'replies.user', 'replies.replyToComment.user'])
                                ->latest()
                                ->paginate(10, ['*'], 'comments_page', $commentsPage);

                            $paginated = $commentsQuery->toArray();
                            $paginated['data'] = PostCommentResource::collection($commentsQuery->getCollection())->resolve();

                            return $paginated;
                        })->merge()->append('data'),
                    ]
                ),
            ],
            'relatedPosts' => Inertia::defer(fn () => BlogPostResource::collection($this->blogQueryService->relatedPosts($post))->resolve()),
            'popularPosts' => Inertia::defer(fn () => BlogPostResource::collection($this->blogQueryService->popularPosts())->resolve()),
            'categories' => Inertia::defer(fn () => $this->blogQueryService->categories()),
            'tags' => Inertia::defer(fn () => $this->blogQueryService->tags()),
        ])->withViewData([
            'meta' => $this->pageMeta->forPost($post),
        ]);
    }

    public function preview(Request $request, Post $post): Response
    {
        $previewData = Cache::get('post_preview_'.$post->preview_token);

        if ($previewData) {
            $post->title = is_array($previewData['title'] ?? null)
                ? json_encode($previewData['title'])
                : ($previewData['title'] ?? $post->title);

            if (isset($previewData['slug'])) {
                $post->slug = is_array($previewData['slug']) ? json_encode($previewData['slug']) : $previewData['slug'];
            }

            if (isset($previewData['content'])) {
                $content = $previewData['content'];
                if (is_array($content)) {
                    if (isset($content['type']) && $content['type'] === 'doc') {
                        try {
                            $content = (new Editor)->setContent($content)->getHTML();
                        } catch (\Exception $e) {
                            $content = json_encode($content);
                        }
                    } else {
                        $resolved = $content['html'] ?? $content['content'] ?? null;
                        if ($resolved !== null && ! is_array($resolved)) {
                            $content = $resolved;
                        } else {
                            $content = json_encode($content);
                        }
                    }
                }
                $post->content = (string) $content;
            }

            if (isset($previewData['cover_image'])) {
                $cover = $previewData['cover_image'];
                if (is_array($cover)) {
                    $resolved = Arr::first($cover) ?? $post->cover_image;
                    if (is_array($resolved)) {
                        $resolved = json_encode($resolved);
                    }
                    $cover = $resolved;
                }
                $post->cover_image = (string) $cover;
            }

            $post->allow_comments = (bool) ($previewData['allow_comments'] ?? $post->allow_comments);

            if (isset($previewData['categories'])) {
                $categories = PostCategory::whereIn('id', (array) $previewData['categories'])->get();
                $post->setRelation('categories', $categories);
            }

            if (isset($previewData['tags'])) {
                $tags = PostTag::whereIn('id', (array) $previewData['tags'])->get();
                $post->setRelation('tags', $tags);
            }
        }

        $post->loadMissing([
            'user:id,name,avatar_url',
            'reviewedBy:id,name',
            'categories:id,name,slug',
            'tags:id,name,slug',
        ]);

        $postResource = new BlogPostResource($post);

        $commentsPage = request()->integer('comments_page', 1);

        return Inertia::render('blog/show', [
            'isPreview' => true,
            'post' => [
                'data' => array_merge(
                    $postResource->resolve(),
                    [
                        'commentsCount' => $post->comments()->count(),
                        'comments' => Inertia::defer(function () use ($post, $commentsPage) {
                            $commentsQuery = $post->comments()
                                ->whereNull('parent_id')
                                ->with(['user', 'replies.user', 'replies.replyToComment.user'])
                                ->latest()
                                ->paginate(10, ['*'], 'comments_page', $commentsPage);

                            $paginated = $commentsQuery->toArray();
                            $paginated['data'] = PostCommentResource::collection($commentsQuery->getCollection())->resolve();

                            return $paginated;
                        })->merge()->append('data'),
                    ]
                ),
            ],
            'relatedPosts' => BlogPostResource::collection($this->blogQueryService->relatedPosts($post))->resolve(),
            'popularPosts' => BlogPostResource::collection($this->blogQueryService->popularPosts())->resolve(),
            'categories' => $this->blogQueryService->categories(),
            'tags' => $this->blogQueryService->tags(),
        ])->withViewData([
            'meta' => $this->pageMeta->forPost($post),
        ]);
    }
}
