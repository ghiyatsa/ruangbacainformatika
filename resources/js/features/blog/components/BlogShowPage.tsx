import { Deferred, Link, usePage } from '@inertiajs/react';
import { Eye, Tag, Bookmark, Share2 } from 'lucide-react';
import { toast } from 'sonner';
import { PageLayout } from '@/components/layout/PageLayout';
import { StaticPageContent } from '@/components/layout/StaticPageContent';
import { Badge } from '@/components/ui/badge';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { Button } from '@/components/ui/button';
import { useCatalogBookmarks } from '@/features/books/hooks/use-catalog-bookmarks';
import blog from '@/routes/blog';
import { BlogLabelsSidebar } from './BlogLabelsSidebar';
import { BlogPopularPosts } from './BlogPopularPosts';
import { BlogPostCard } from './BlogPostCard';
import {
    BlogPostCardSkeleton,
    BlogPopularPostsSkeleton,
    BlogLabelsSidebarSkeleton,
} from './BlogPostCardSkeleton';
import type { BlogShowPageProps } from '@/features/blog/types';

export function BlogShowPage({
    post,
    relatedPosts,
    popularPosts,
    categories,
    tags,
}: BlogShowPageProps) {
    const article = post.data;
    const page = usePage<any>();
    const shareUrl = `${page.props.site?.url ?? ''}${page.url}`;
    const shareTitle = article.title;
    const shareText = article.summary ?? article.excerpt;

    const { isBookmarked, toggleBookmark } = useCatalogBookmarks();

    const isBookmarkedByUser = isBookmarked({
        catalogType: 'post',
        id: article.id,
    });

    const bookmarkRecord = {
        catalogType: 'post' as const,
        id: article.id,
        href: shareUrl,
        title: article.title,
        subtitle: article.summary ?? article.excerpt,
        meta: article.author?.name ?? null,
        year: article.publishedAt
            ? new Date(article.publishedAt).getFullYear()
            : null,
        coverImageUrl: article.coverImageUrl,
        kindLabel: 'Artikel',
        statusLabel: null,
    };

    const handleShare = async () => {
        try {
            if (navigator.share) {
                await navigator.share({
                    title: shareTitle,
                    text: shareText,
                    url: shareUrl,
                });

                return;
            }

            if (typeof navigator !== 'undefined' && navigator.clipboard) {
                await navigator.clipboard.writeText(shareUrl);
                toast.success('Tautan berhasil disalin.');
            }
        } catch (error) {
            if (error instanceof DOMException && error.name === 'AbortError') {
                return;
            }

            toast.error('Gagal membagikan tautan.');
        }
    };

    return (
        <PageLayout
            title={article.title}
            metaDescription={article.summary ?? article.excerpt}
            maxWidth="7xl"
            className="pt-0 pb-16"
            showDesktopNoticeInContent={false}
            header={
                <div className="relative -mt-20 overflow-hidden bg-background sm:-mt-28 md:-mt-24">
                    <div className="relative mx-auto max-w-7xl px-4 pt-24 sm:px-6 sm:pt-30 lg:px-8">
                        {/* Breadcrumbs bar — identical style to CatalogHeader */}
                        <div className="-mx-4 mb-6 hidden border-y border-border/60 bg-muted/5 px-4 py-3 sm:-mx-6 sm:flex sm:items-center sm:px-6 lg:-mx-8 lg:px-8">
                            <Breadcrumb>
                                <BreadcrumbList>
                                    <BreadcrumbItem>
                                        <BreadcrumbLink asChild>
                                            <Link href="/">Beranda</Link>
                                        </BreadcrumbLink>
                                    </BreadcrumbItem>
                                    <BreadcrumbSeparator />
                                    <BreadcrumbItem>
                                        <BreadcrumbLink asChild>
                                            <Link href={blog.index.url()}>
                                                Artikel
                                            </Link>
                                        </BreadcrumbLink>
                                    </BreadcrumbItem>
                                    <BreadcrumbSeparator />
                                    <BreadcrumbItem>
                                        <BreadcrumbPage className="max-w-xs truncate">
                                            {article.title}
                                        </BreadcrumbPage>
                                    </BreadcrumbItem>
                                </BreadcrumbList>
                            </Breadcrumb>
                        </div>

                        {/* Article title block */}
                        <div className="pt-4 pb-8 sm:pt-0">
                            {article.categories.length > 0 && (
                                <div className="mb-3 flex flex-wrap gap-2">
                                    {article.categories.map((cat) => (
                                        <Link
                                            key={cat.slug}
                                            href={blog.index.url({
                                                query: { category: cat.slug },
                                            })}
                                        >
                                            <Badge className="rounded-full">
                                                {cat.name}
                                            </Badge>
                                        </Link>
                                    ))}
                                </div>
                            )}

                            <h1 className="text-wrap-balance text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">
                                {article.title}
                            </h1>

                            {article.summary && (
                                <p className="mt-3 max-w-3xl text-base text-muted-foreground sm:text-lg">
                                    {article.summary}
                                </p>
                            )}

                            {/* Author & Action buttons section */}
                            <div className="mt-4 flex flex-wrap items-center justify-between gap-4">
                                {article.author ? (
                                    <div className="flex items-center gap-2.5">
                                        {article.author.avatar ? (
                                            <img
                                                src={article.author.avatar}
                                                alt=""
                                                className="h-8 w-8 rounded-full border border-border/40 object-cover"
                                            />
                                        ) : (
                                            <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-foreground">
                                                {article.author.name
                                                    .charAt(0)
                                                    .toUpperCase()}
                                            </div>
                                        )}
                                        <div className="flex flex-col text-sm">
                                            <span className="text-muted-foreground">
                                                Published by{' '}
                                            </span>
                                            <span className="font-semibold text-primary">
                                                {article.author.name}
                                            </span>
                                        </div>
                                    </div>
                                ) : (
                                    <div />
                                )}

                                <div className="flex items-center gap-2">
                                    {/* Bookmark Button */}
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="icon"
                                        className="h-8 w-8 rounded-xl"
                                        onClick={() =>
                                            toggleBookmark(bookmarkRecord)
                                        }
                                        title={
                                            isBookmarkedByUser
                                                ? 'Hapus bookmark'
                                                : 'Simpan bookmark'
                                        }
                                    >
                                        <Bookmark
                                            className={`size-4 text-primary transition-colors ${
                                                isBookmarkedByUser
                                                    ? 'fill-current'
                                                    : ''
                                            }`}
                                        />
                                    </Button>

                                    {/* Share Button */}
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="icon"
                                        className="h-8 w-8 rounded-xl"
                                        onClick={handleShare}
                                        title="Bagikan artikel"
                                    >
                                        <Share2 className="size-4 text-primary" />
                                    </Button>
                                </div>
                            </div>

                            {/* Meta info section */}
                            <div className="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-muted-foreground">
                                <span>
                                    Diterbitkan:{' '}
                                    {article.publishedAtLabel ?? 'Belum terbit'}
                                </span>
                                {article.updatedAtLabel && (
                                    <>
                                        <span className="text-muted-foreground/30">
                                            •
                                        </span>
                                        <span>
                                            Diperbarui: {article.updatedAtLabel}
                                        </span>
                                    </>
                                )}
                                <span className="text-muted-foreground/30">
                                    •
                                </span>
                                <span>
                                    Waktu baca: {article.readingMinutes} menit
                                </span>
                                <span className="text-muted-foreground/30">
                                    •
                                </span>
                                <span className="inline-flex items-center gap-1">
                                    <Eye className="size-3.5" />
                                    {article.viewCount.toLocaleString('id-ID')}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            }
        >
            {/* Two-column: article body + sidebar */}
            <div className="grid gap-8 xl:grid-cols-[minmax(0,1fr)_22rem]">
                {/* ─── LEFT: Article content ─── */}
                <div className="space-y-8">
                    <div className="space-y-6">
                        {/* Cover image */}
                        <section className="overflow-hidden rounded-2xl border border-border/60 bg-card">
                            <div className="aspect-16/8 bg-muted">
                                <img
                                    src={article.coverImageUrl}
                                    alt=""
                                    width={1200}
                                    height={600}
                                    className="h-full w-full object-cover"
                                />
                            </div>
                        </section>

                        {/* Article body */}
                        <StaticPageContent
                            html={article.content}
                            className="max-w-none"
                        />
                    </div>

                    {/* Related posts (Moved below content) */}
                    <Deferred
                        data="relatedPosts"
                        fallback={
                            <section className="border-t border-border/60 pt-8">
                                <h2 className="mb-6 text-xl font-bold text-foreground">
                                    Artikel Terkait
                                </h2>
                                <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                    {Array.from({ length: 3 }).map((_, idx) => (
                                        <BlogPostCardSkeleton key={idx} />
                                    ))}
                                </div>
                            </section>
                        }
                    >
                        {relatedPosts && relatedPosts.length > 0 && (
                            <section className="border-t border-border/60 pt-8">
                                <h2 className="mb-6 text-xl font-bold text-foreground">
                                    Artikel Terkait
                                </h2>
                                <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                    {relatedPosts.map((relatedPost) => (
                                        <BlogPostCard
                                            key={relatedPost.id}
                                            post={relatedPost}
                                        />
                                    ))}
                                </div>
                            </section>
                        )}
                    </Deferred>
                </div>

                {/* ─── RIGHT: Sidebar ─── */}
                <aside className="space-y-6">
                    {/* Tags */}
                    <section className="overflow-hidden rounded-2xl border border-border/60 bg-card">
                        <div className="border-b border-border/60 px-5 py-3.5">
                            <h2 className="text-sm font-bold tracking-wide text-foreground">
                                Tag
                            </h2>
                        </div>
                        <div className="p-4">
                            {article.tags.length > 0 ? (
                                <div className="flex flex-wrap gap-2">
                                    {article.tags.map((tagItem) => (
                                        <Link
                                            key={tagItem.slug}
                                            href={blog.index.url({
                                                query: { tag: tagItem.slug },
                                            })}
                                        >
                                            <Badge
                                                variant="secondary"
                                                className="rounded-full transition-colors hover:bg-primary/10 hover:text-primary"
                                            >
                                                <Tag className="mr-1 size-3" />
                                                {tagItem.name}
                                            </Badge>
                                        </Link>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-sm text-muted-foreground">
                                    Artikel ini belum memiliki tag.
                                </p>
                            )}
                        </div>
                    </section>

                    {/* Popular Posts */}
                    <Deferred
                        data="popularPosts"
                        fallback={<BlogPopularPostsSkeleton />}
                    >
                        {popularPosts && (
                            <BlogPopularPosts posts={popularPosts} />
                        )}
                    </Deferred>

                    {/* Labels & Categories */}
                    <Deferred
                        data={['categories', 'tags']}
                        fallback={<BlogLabelsSidebarSkeleton />}
                    >
                        {categories && tags && (
                            <BlogLabelsSidebar
                                categories={categories}
                                tags={tags}
                            />
                        )}
                    </Deferred>
                </aside>
            </div>
        </PageLayout>
    );
}
