import { Link } from '@inertiajs/react';
import blog from '@/routes/blog';
import type { BlogPostItem } from '@/features/blog/types';

interface BlogPopularPostsProps {
    posts: BlogPostItem[];
}

export function BlogPopularPosts({ posts }: BlogPopularPostsProps) {
    if (posts.length === 0) {
        return null;
    }

    return (
        <section className="overflow-hidden rounded-2xl border border-border/60 bg-card">
            {/* Header */}
            <div className="flex items-center justify-between border-b border-border/60 px-5 py-3.5">
                <h2 className="text-sm font-bold tracking-wide text-foreground">
                    Artikel Populer
                </h2>
            </div>

            {/* Top post thumbnail */}
            <div className="relative">
                <Link
                    href={blog.show.url(posts[0].slug)}
                    className="group block"
                >
                    <div className="aspect-video overflow-hidden bg-muted">
                        <img
                            src={posts[0].coverImageUrl}
                            alt={posts[0].title}
                            width={640}
                            height={360}
                            className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]"
                        />
                    </div>
                    <div className="absolute inset-0 bg-linear-to-t from-black/60 to-transparent" />
                    <div className="absolute bottom-0 p-3">
                        {posts[0].author && (
                            <div className="mb-1.5 flex items-center gap-1.5 rounded-full bg-background px-2 py-1">
                                <div className="flex size-4 items-center justify-center rounded-full bg-primary text-[9px] font-bold text-primary-foreground">
                                    {posts[0].author.name
                                        .charAt(0)
                                        .toUpperCase()}
                                </div>
                                <span className="text-xs font-medium text-foreground">
                                    {posts[0].author.name}
                                </span>
                            </div>
                        )}
                    </div>
                </Link>
            </div>

            {/* Ranked list */}
            <div className="divide-y divide-border/50">
                {posts.map((post, idx) => (
                    <Link
                        key={post.id}
                        href={blog.show.url(post.slug)}
                        className="group flex gap-3 p-4 transition-colors hover:bg-muted/50"
                    >
                        {/* Rank number */}
                        <span className="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded text-sm font-bold text-primary">
                            #{idx + 1}
                        </span>

                        {/* Content */}
                        <div className="min-w-0 flex-1 space-y-1">
                            {post.categories[0] && (
                                <p className="text-[11px] font-medium text-primary">
                                    {post.categories[0].name}
                                </p>
                            )}
                            <p className="line-clamp-2 text-sm leading-snug font-semibold text-foreground transition-colors group-hover:text-primary">
                                {post.title}
                            </p>
                            <div className="space-x-1">
                                <span className="rounded-xs bg-primary px-1 py-0.5 text-xs text-primary-foreground">
                                    Updated
                                </span>
                                <span className="inline-flex items-center gap-1 text-xs">
                                    {post.publishedAtLabel ?? 'Belum terbit'}
                                </span>
                            </div>
                        </div>
                    </Link>
                ))}
            </div>
        </section>
    );
}
