import { Link, usePage } from '@inertiajs/react';
import { ArrowUpRight, CalendarDays, Bookmark } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useCatalogBookmarks } from '@/features/books/hooks/use-catalog-bookmarks';
import { instantLoadingPageProps } from '@/lib/inertia-loading';
import blog from '@/routes/blog';
import type { BlogPostItem } from '@/features/blog/types';

interface BlogPostCardProps {
    post: BlogPostItem;
    compact?: boolean;
}

export function BlogPostCard({ post, compact = false }: BlogPostCardProps) {
    const { isBookmarked, toggleBookmark } = useCatalogBookmarks();
    const page = usePage<any>();

    const isBookmarkedByUser = isBookmarked({
        catalogType: 'post',
        id: post.id,
    });

    const postUrl = `${page.props.site?.url ?? ''}${blog.show.url(post.slug)}`;

    const bookmarkRecord = {
        catalogType: 'post' as const,
        id: post.id,
        href: postUrl,
        title: post.title,
        subtitle: post.summary ?? post.excerpt,
        meta: post.author?.name ?? null,
        year: post.publishedAt
            ? new Date(post.publishedAt).getFullYear()
            : null,
        coverImageUrl: post.coverImageUrl,
        kindLabel: 'Artikel',
        statusLabel: null,
    };

    if (compact) {
        // Compact horizontal card — used in related posts sidebar
        return (
            <Link
                href={blog.show.url(post.slug)}
                instant
                component="blog/show"
                pageProps={instantLoadingPageProps()}
                className="group flex gap-3 overflow-hidden rounded-xl border border-border/60 bg-card p-3 transition-all duration-200 hover:border-primary/30"
            >
                <div className="aspect-square size-16 shrink-0 overflow-hidden rounded-lg bg-muted">
                    <img
                        src={post.coverImageUrl}
                        alt=""
                        width={64}
                        height={64}
                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                </div>
                <div className="min-w-0 flex-1 space-y-1">
                    {post.categories[0] && (
                        <p className="text-[11px] text-primary">
                            {post.categories[0].name}
                        </p>
                    )}
                    <p className="line-clamp-2 text-sm leading-snug font-semibold text-foreground transition-colors group-hover:text-primary">
                        {post.title}
                    </p>
                    <p className="flex items-center gap-1 text-[11px] text-muted-foreground">
                        <CalendarDays className="size-3" />
                        {post.publishedAtLabel ?? '—'}
                    </p>
                </div>
            </Link>
        );
    }

    // Standard vertical card — used in grid
    return (
        <Link
            href={blog.show.url(post.slug)}
            instant
            component="blog/show"
            pageProps={instantLoadingPageProps()}
            className="group flex h-full flex-col overflow-hidden rounded-2xl border border-border/60 bg-card transition-all duration-300 hover:border-primary/30"
        >
            {/* Cover */}
            <div className="relative aspect-16/10 overflow-hidden bg-muted">
                <img
                    src={post.coverImageUrl}
                    alt=""
                    width={640}
                    height={400}
                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]"
                />

                {/* Bookmark Button */}
                <div className="absolute top-2 right-2 z-20">
                    <Button
                        type="button"
                        size="icon"
                        variant="secondary"
                        title={
                            isBookmarkedByUser
                                ? 'Hapus bookmark'
                                : 'Simpan bookmark'
                        }
                        className={`h-7 w-7 rounded-xl border border-white/20 bg-black/55 text-white transition-colors duration-200 hover:bg-black/70 hover:text-white ${
                            isBookmarkedByUser
                                ? 'border-primary/40 bg-primary text-primary-foreground hover:bg-primary/90'
                                : ''
                        }`}
                        onClick={(event) => {
                            event.preventDefault();
                            event.stopPropagation();
                            toggleBookmark(bookmarkRecord);
                        }}
                    >
                        <Bookmark
                            className={`size-3.5 transition-transform duration-200 group-hover:scale-105 ${
                                isBookmarkedByUser ? 'fill-current' : ''
                            }`}
                        />
                    </Button>
                </div>

                {/* Author avatar (top-left) */}
                {post.author && (
                    <div className="absolute bottom-3 left-3 flex items-center gap-1.5 rounded-full bg-background px-2 py-1 backdrop-blur-sm dark:bg-primary">
                        <div className="flex size-4 items-center justify-center rounded-full bg-primary text-[9px] font-bold text-primary-foreground dark:bg-background dark:text-primary">
                            {post.author.name.charAt(0).toUpperCase()}
                        </div>
                        <span className="text-[11px] font-medium text-foreground dark:text-primary-foreground">
                            {post.author.name}
                        </span>
                    </div>
                )}
            </div>

            {/* Body */}
            <div className="flex flex-1 flex-col gap-3 p-4">
                {/* Categories */}
                {post.categories.length > 0 && (
                    <div className="flex flex-wrap items-center gap-1.5">
                        {post.categories.slice(0, 2).map((cat) => (
                            <span
                                key={cat.slug}
                                className="text-[11px] font-medium text-primary"
                            >
                                {cat.name}
                            </span>
                        ))}
                        {post.categories.length > 2 && (
                            <span className="rounded bg-muted px-1.5 py-0.5 text-[9px] font-semibold text-muted-foreground">
                                +{post.categories.length - 2}
                            </span>
                        )}
                    </div>
                )}

                {/* Title + excerpt */}
                <div className="space-y-1.5">
                    <h3 className="text-wrap-balance line-clamp-2 leading-snug font-bold text-foreground transition-colors group-hover:text-primary">
                        {post.title}
                    </h3>
                    <p className="line-clamp-2 text-sm leading-relaxed text-muted-foreground">
                        {post.excerpt}
                    </p>
                </div>

                {/* Meta footer */}
                <div className="mt-auto flex flex-wrap items-center gap-x-3 gap-y-1 pt-1 text-xs text-muted-foreground">
                    <div className="space-x-1">
                        <span className="rounded-xs bg-primary px-1 py-0.5 text-primary-foreground">
                            Updated
                        </span>
                        <span className="inline-flex items-center gap-1">
                            {post.publishedAtLabel ?? 'Belum terbit'}
                        </span>
                    </div>

                    <span className="ml-auto inline-flex items-center gap-0.5 font-medium text-primary transition">
                        Baca artikel
                        <ArrowUpRight className="size-3.5" />
                    </span>
                </div>
            </div>
        </Link>
    );
}
