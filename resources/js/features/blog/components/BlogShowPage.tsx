import { Deferred, Link, usePage, WhenVisible } from '@inertiajs/react';
import { Eye, Tag, Bookmark, Share2 } from 'lucide-react';
import { useState } from 'react';
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
import { Skeleton } from '@/components/ui/skeleton';
import { BlogCommentsSection } from '@/features/blog/components/comments/BlogCommentsSection';
import { useCatalogBookmarks } from '@/features/books/hooks/use-catalog-bookmarks';
import { cn, formatViewCount } from '@/lib/utils';
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
    isPreview = false,
}: BlogShowPageProps) {
    const [imageLoaded, setImageLoaded] = useState(false);
    const article = post?.data ?? null;
    const page = usePage<any>();
    const shareUrl = `${page.props.site?.url ?? ''}${page.url}`;
    const shareTitle = article?.title ?? 'Memuat...';
    const shareText = article?.summary ?? article?.excerpt ?? 'Memuat...';

    const { isBookmarked, toggleBookmark } = useCatalogBookmarks();

    const isBookmarkedByUser = article
        ? isBookmarked({
              catalogType: 'post',
              id: article.id,
          })
        : false;

    const bookmarkRecord = article
        ? {
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
          }
        : null;

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
            title={article?.title ?? 'Memuat Artikel...'}
            metaDescription={(() => {
                const text = article?.summary ?? article?.excerpt;

                if (text) {
                    if (text.length >= 120) {
                        return text.slice(0, 160);
                    }

                    return `${text} Baca artikel lengkap dan berita terbaru seputar akademik, kemahasiswaan, dan teknologi di blog resmi Ruang Baca Teknik Informatika Unimal.`.slice(0, 160);
                }

                return 'Baca artikel terbaru, berita pengumuman, tips pemrograman, panduan akademik, dan info seputar kegiatan mahasiswa di blog resmi Ruang Baca Teknik Informatika Universitas Malikussaleh.';
            })()}
            image={article?.coverImageUrl || undefined}
            type="article"
            maxWidth="7xl"
            className="pt-6 pb-16 sm:pt-8"
            showDesktopNoticeInContent={false}
            header={
                <div className="relative -mt-20 overflow-hidden border-b bg-background sm:-mt-28 md:-mt-24">
                    <div className="relative mx-auto max-w-7xl px-4 pt-24 pb-6 sm:px-6 sm:pt-30 sm:pb-8 lg:px-8">
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
                                            {article ? (
                                                article.title
                                            ) : (
                                                <Skeleton className="h-4 w-32 animate-pulse" />
                                            )}
                                        </BreadcrumbPage>
                                    </BreadcrumbItem>
                                </BreadcrumbList>
                            </Breadcrumb>
                        </div>

                        {/* Article title block */}
                        <div className="pt-4 sm:pt-0">
                            {article ? (
                                article.categories.length > 0 && (
                                    <div className="mb-3 flex flex-wrap gap-2">
                                        {article.categories.map((cat) => (
                                            <Link
                                                key={cat.slug}
                                                href={blog.index.url({
                                                    query: {
                                                        category: cat.slug,
                                                    },
                                                })}
                                            >
                                                <Badge className="rounded-full">
                                                    {cat.name}
                                                </Badge>
                                            </Link>
                                        ))}
                                    </div>
                                )
                            ) : (
                                <div className="mb-3 flex flex-wrap gap-2">
                                    <Skeleton className="h-6 w-16 animate-pulse rounded-full" />
                                    <Skeleton className="h-6 w-20 animate-pulse rounded-full" />
                                </div>
                            )}
                            {article ? (
                                <>
                                    <h1 className="text-wrap-balance text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">
                                        {article.title}
                                    </h1>
                                </>
                            ) : (
                                <>
                                    <div className="max-w-3xl space-y-2">
                                        <Skeleton className="h-8 w-11/12 animate-pulse sm:h-10 lg:h-12" />
                                        <Skeleton className="h-8 w-2/3 animate-pulse sm:h-10 lg:h-12" />
                                    </div>
                                </>
                            )}
                            {/* Author & Action buttons section */}
                            <div className="mt-4 flex flex-wrap items-center justify-between gap-4">
                                {article ? (
                                    article.author ? (
                                        <div className="flex items-center gap-2.5">
                                            {article.author.avatar ? (
                                                <img
                                                    src={article.author.avatar}
                                                    alt=""
                                                    className="h-8 w-8 rounded-full border border-border/40 object-cover"
                                                />
                                            ) : (
                                                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-foreground">
                                                    {article.author.initials ??
                                                        '?'}
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
                                    )
                                ) : (
                                    <div className="flex items-center gap-2.5">
                                        <Skeleton className="h-8 w-8 animate-pulse rounded-full" />
                                        <div className="flex flex-col text-sm">
                                            <span className="text-muted-foreground">
                                                Published by{' '}
                                            </span>
                                            <Skeleton className="mt-0.5 h-4 w-24 animate-pulse" />
                                        </div>
                                    </div>
                                )}

                                <div className="flex items-center gap-2">
                                    {/* Bookmark Button */}
                                    {article && bookmarkRecord ? (
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
                                    ) : (
                                        <Skeleton className="h-8 w-8 animate-pulse rounded-xl" />
                                    )}

                                    {/* Share Button */}
                                    {article ? (
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
                                    ) : (
                                        <Skeleton className="h-8 w-8 animate-pulse rounded-xl" />
                                    )}
                                </div>
                            </div>{' '}
                            {/* Meta info section */}
                            {article ? (
                                <div className="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-muted-foreground">
                                    <span>
                                        Diterbitkan:{' '}
                                        {article.publishedAtLabel ??
                                            'Belum terbit'}
                                    </span>
                                    {article.updatedAtLabel && (
                                        <>
                                            <span className="text-muted-foreground/30">
                                                •
                                            </span>
                                            <span>
                                                Diperbarui:{' '}
                                                {article.updatedAtLabel}
                                            </span>
                                        </>
                                    )}
                                    <span className="text-muted-foreground/30">
                                        •
                                    </span>
                                    <span>
                                        Waktu baca: {article.readingMinutes}{' '}
                                        menit
                                    </span>
                                    <span className="text-muted-foreground/30">
                                        •
                                    </span>
                                    <span className="inline-flex items-center gap-1">
                                        <Eye className="size-3.5" />
                                        {formatViewCount(article.viewCount)}
                                    </span>
                                </div>
                            ) : (
                                <div className="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-muted-foreground">
                                    <span>
                                        Diterbitkan:{' '}
                                        <Skeleton className="inline-block h-3 w-16 animate-pulse align-middle" />
                                    </span>
                                    <span className="text-muted-foreground/30">
                                        •
                                    </span>
                                    <span>
                                        Waktu baca:{' '}
                                        <Skeleton className="inline-block h-3 w-6 animate-pulse align-middle" />{' '}
                                        menit
                                    </span>
                                    <span className="text-muted-foreground/30">
                                        •
                                    </span>
                                    <span className="inline-flex items-center gap-1">
                                        <Eye className="size-3.5" />
                                        <Skeleton className="inline-block h-3 w-10 animate-pulse align-middle" />
                                    </span>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            }
        >
            {(() => {
                const jsonLd = article
                    ? {
                          '@context': 'https://schema.org',
                          '@type': 'BlogPosting',
                          headline: article.title,
                          image: article.coverImageUrl || undefined,
                          datePublished: article.publishedAt || undefined,
                          dateModified: article.updatedAt || undefined,
                          author: article.author
                              ? {
                                    '@type': 'Person',
                                    name: article.author.name,
                                }
                              : undefined,
                          description:
                              article.summary || article.excerpt || undefined,
                          publisher: {
                              '@type': 'Organization',
                              name:
                                  page.props.name ||
                                  'Ruang Baca Teknik Informatika UNIMAL',
                              logo: page.props.site?.logo
                                  ? {
                                        '@type': 'ImageObject',
                                        url: page.props.site.logo,
                                    }
                                  : undefined,
                          },
                      }
                    : null;

                if (!jsonLd) {
                    return null;
                }

                return (
                    <script type="application/ld+json">
                        {JSON.stringify(jsonLd)}
                    </script>
                );
            })()}

            {/* Two-column: article body + sidebar */}
            <div className="grid gap-8 xl:grid-cols-[minmax(0,1fr)_22rem]">
                {/* ─── LEFT: Article content ─── */}
                <div className="space-y-8">
                    {article && article.status !== 'approved' && (
                        <div className="text-yellow-850 flex items-center gap-2 rounded-2xl border border-yellow-500/30 bg-yellow-500/10 p-4 text-sm dark:text-yellow-400">
                            <span className="relative flex h-2 w-2 shrink-0">
                                <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-yellow-400 opacity-75"></span>
                                <span className="relative inline-flex h-2 w-2 rounded-full bg-yellow-500"></span>
                            </span>
                            <span>
                                <strong>Mode Pratinjau:</strong> Artikel ini
                                masih berstatus{' '}
                                <strong>
                                    {article.status === 'draft'
                                        ? 'Draf'
                                        : article.status === 'pending'
                                          ? 'Dalam Peninjauan'
                                          : 'Perlu Perbaikan'}
                                </strong>{' '}
                                dan belum terbit publik.
                            </span>
                        </div>
                    )}
                    <div className="space-y-6">
                        {/* Cover image */}
                        <section className="overflow-hidden rounded-2xl border border-border/60 bg-card">
                            <div className="relative aspect-video bg-muted">
                                {article && (
                                    <img
                                        src={article.coverImageUrl}
                                        alt=""
                                        width={1200}
                                        height={600}
                                        onLoad={() => setImageLoaded(true)}
                                        className={cn(
                                            'h-full w-full object-cover transition-opacity duration-300',
                                            imageLoaded
                                                ? 'opacity-100'
                                                : 'absolute opacity-0',
                                        )}
                                    />
                                )}
                                {(!article || !imageLoaded) && (
                                    <Skeleton className="absolute inset-0 h-full w-full animate-pulse" />
                                )}
                            </div>
                        </section>

                        {/* Article body */}
                        {article ? (
                            <StaticPageContent
                                html={article.content}
                                className="max-w-none"
                            />
                        ) : (
                            <div className="max-w-none space-y-4 pt-4">
                                <Skeleton className="h-4 w-full animate-pulse" />
                                <Skeleton className="h-4 w-11/12 animate-pulse" />
                                <Skeleton className="h-4 w-10/12 animate-pulse" />
                                <Skeleton className="h-4 w-5/6 animate-pulse" />
                                <div className="h-4" />
                                <Skeleton className="h-4 w-full animate-pulse" />
                                <Skeleton className="h-4 w-full animate-pulse" />
                                <Skeleton className="h-4 w-4/5 animate-pulse" />
                            </div>
                        )}
                    </div>

                    {/* Comments Section */}
                    {article && (
                        <WhenVisible
                            data="post.data.comments"
                            fallback={
                                <section className="border-t border-border/60 pt-8">
                                    <h3 className="mb-4 text-lg font-bold text-foreground">
                                        Memuat Komentar...
                                    </h3>
                                    <div className="space-y-4">
                                        <div className="flex gap-3">
                                            <div className="size-10 animate-pulse rounded-full bg-muted" />
                                            <div className="flex-1 animate-pulse space-y-2 py-1">
                                                <div className="h-4 w-1/4 rounded bg-muted" />
                                                <div className="h-4 w-3/4 rounded bg-muted" />
                                            </div>
                                        </div>
                                        <div className="flex gap-3 pl-8">
                                            <div className="size-8 animate-pulse rounded-full bg-muted" />
                                            <div className="flex-1 animate-pulse space-y-2 py-1">
                                                <div className="h-4 w-1/4 rounded bg-muted" />
                                                <div className="h-4 w-2/3 rounded bg-muted" />
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            }
                        >
                            <BlogCommentsSection
                                comments={article.comments?.data ?? []}
                                commentsCount={article.commentsCount}
                                articleSlug={article.slug}
                                allowComments={article.allowComments}
                                currentUser={page.props.auth?.user}
                                googleLoginUrl={
                                    page.props.googleAuth?.loginUrl ??
                                    '/auth/google'
                                }
                                pagination={article.comments}
                            />
                        </WhenVisible>
                    )}

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
                            {article ? (
                                article.tags.length > 0 ? (
                                    <div className="flex flex-wrap gap-2">
                                        {article.tags.map((tagItem) => (
                                            <Link
                                                key={tagItem.slug}
                                                href={blog.index.url({
                                                    query: {
                                                        tag: tagItem.slug,
                                                    },
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
                                )
                            ) : (
                                <div className="flex flex-wrap gap-2">
                                    <Skeleton className="h-6 w-16 animate-pulse rounded-full" />
                                    <Skeleton className="h-6 w-12 animate-pulse rounded-full" />
                                    <Skeleton className="h-6 w-20 animate-pulse rounded-full" />
                                </div>
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
            {isPreview && (
                <div className="pointer-events-none fixed right-6 bottom-6 z-9999 select-none">
                    <div className="flex animate-pulse items-center gap-2 rounded-full bg-yellow-500 px-4 py-2 text-xs font-bold text-black shadow-lg dark:bg-yellow-400 dark:text-black">
                        <span className="relative flex h-2 w-2">
                            <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-black opacity-75"></span>
                            <span className="relative inline-flex h-2 w-2 rounded-full bg-black"></span>
                        </span>
                        MODE PRATINJAU
                    </div>
                </div>
            )}
        </PageLayout>
    );
}
