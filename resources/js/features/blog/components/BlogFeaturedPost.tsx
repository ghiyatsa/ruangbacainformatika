import { Link } from '@inertiajs/react';
import { ArrowRight, Clock3, Eye } from 'lucide-react';
import blog from '@/routes/blog';
import type { BlogPostItem } from '@/features/blog/types';

interface BlogFeaturedPostProps {
    post: BlogPostItem;
}

export function BlogFeaturedPost({ post }: BlogFeaturedPostProps) {
    return (
        <Link
            href={blog.show.url(post.slug)}
            className="group relative block overflow-hidden rounded-2xl border border-border/60 bg-card transition-all duration-300 hover:border-primary/40"
        >
            {/* Cover image */}
            <div className="aspect-square sm:aspect-video overflow-hidden bg-muted">
                <img
                    src={post.coverImageUrl}
                    alt={post.title}
                    width={1200}
                    height={675}
                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                />
            </div>

            {/* Overlay gradient + content */}
            <div className="absolute inset-0 bg-linear-to-t from-black/90 via-black/50 to-transparent" />

            <div className="absolute right-0 bottom-0 left-0 p-5 sm:p-6">
                {/* Categories */}
                {post.categories.length > 0 && (
                    <div className="mb-3 flex flex-wrap gap-1.5">
                        {post.categories.slice(0, 2).map((cat) => (
                            <span
                                key={cat.slug}
                                className="inline-flex items-center rounded-full bg-primary/90 px-2.5 py-0.5 text-[11px] font-semibold text-primary-foreground backdrop-blur-sm"
                            >
                                {cat.name}
                            </span>
                        ))}
                    </div>
                )}

                <h2 className="text-wrap-balance line-clamp-2 text-xl leading-snug font-bold text-white sm:text-2xl">
                    {post.title}
                </h2>

                <p className="mt-2 line-clamp-2 text-sm leading-relaxed text-white/80">
                    {post.excerpt}
                </p>

                {/* Meta */}
                <div className="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-white/70">
                    {post.author && (
                        <div className="flex items-center gap-1.5 rounded-full bg-background px-2 py-1 backdrop-blur-sm dark:bg-primary">
                            <div className="flex size-4 items-center justify-center rounded-full bg-primary text-[9px] font-bold text-primary-foreground dark:bg-background dark:text-primary">
                                {post.author.name.charAt(0).toUpperCase()}
                            </div>
                            <span className="text-[11px] font-medium text-foreground dark:text-primary-foreground">
                                {post.author.name}
                            </span>
                        </div>
                    )}
                    <div className="space-x-1">
                        <span className="rounded-xs bg-primary px-1 py-0.5 text-primary-foreground">
                            Updated
                        </span>
                        <span className="inline-flex items-center gap-1">
                            {post.publishedAtLabel ?? '—'}
                        </span>
                    </div>
                    <span className="inline-flex items-center gap-1">
                        <Clock3 className="size-3" />
                        {post.readingMinutes} mnt
                    </span>
                    <span className="inline-flex items-center gap-1">
                        <Eye className="size-3" />
                        {post.viewCount.toLocaleString('id-ID')}
                    </span>
                    <span className="ml-auto inline-flex items-center gap-1 font-semibold text-primary-foreground/90 transition group-hover:gap-2">
                        Baca
                        <ArrowRight className="size-3 transition-transform duration-200 group-hover:translate-x-0.5" />
                    </span>
                </div>
            </div>
        </Link>
    );
}
