import { Link } from '@inertiajs/react';
import { ArrowUpRight, CalendarDays, Clock3, Eye } from 'lucide-react';
import blog from '@/routes/blog';
import type { BlogPostItem } from '@/features/blog/types';

interface BlogPostCardProps {
    post: BlogPostItem;
    compact?: boolean;
}

export function BlogPostCard({ post, compact = false }: BlogPostCardProps) {
    return (
        <Link
            href={blog.show.url(post.slug)}
            className={`group overflow-hidden rounded-3xl border border-border/70 bg-card shadow-sm transition hover:border-primary/30 hover:shadow-md ${compact ? 'flex flex-col sm:flex-row' : 'flex h-full flex-col'}`}
        >
            <div
                className={`overflow-hidden bg-muted ${compact ? 'aspect-[16/10] sm:w-56 sm:shrink-0' : 'aspect-[16/10] w-full'}`}
            >
                <img
                    src={post.coverImageUrl}
                    alt=""
                    width={640}
                    height={360}
                    className="h-full w-full object-cover transition duration-300 group-hover:scale-[1.015]"
                />
            </div>

            <div className="flex flex-1 flex-col gap-4 p-5">
                <div className="flex flex-wrap gap-2">
                    {post.categories.slice(0, 2).map((category) => (
                        <span
                            key={`${post.id}-${category.slug}`}
                            className="inline-flex rounded-full bg-primary/8 px-2.5 py-1 text-[11px] font-medium text-primary"
                        >
                            {category.name}
                        </span>
                    ))}
                </div>

                <div className="space-y-2">
                    <h3 className="text-lg font-bold tracking-tight text-foreground transition-colors group-hover:text-primary">
                        {post.title}
                    </h3>
                    <p className="line-clamp-3 text-sm leading-relaxed text-muted-foreground">
                        {post.excerpt}
                    </p>
                </div>

                <div className="mt-auto flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-muted-foreground">
                    <span className="inline-flex items-center gap-1.5">
                        <CalendarDays className="size-3.5" />
                        {post.publishedAtLabel ?? 'Belum terbit'}
                    </span>
                    <span className="inline-flex items-center gap-1.5">
                        <Clock3 className="size-3.5" />
                        {post.readingMinutes} menit baca
                    </span>
                    <span className="inline-flex items-center gap-1.5">
                        <Eye className="size-3.5" />
                        {post.viewCount.toLocaleString('id-ID')} dibaca
                    </span>
                    <span className="inline-flex items-center gap-1.5 font-medium text-primary">
                        Buka artikel
                        <ArrowUpRight className="size-3.5" />
                    </span>
                </div>
            </div>
        </Link>
    );
}
