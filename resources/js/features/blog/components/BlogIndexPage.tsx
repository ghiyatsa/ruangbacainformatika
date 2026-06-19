import { Deferred, Link, router } from '@inertiajs/react';
import { X } from 'lucide-react';
import { Breadcrumbs } from '@/components/common/Breadcrumbs';
import { PageLayout } from '@/components/layout/PageLayout';
import { CatalogPagination } from '@/features/books/components/CatalogPagination';
import blog from '@/routes/blog';
import { BlogFeaturedPost } from './BlogFeaturedPost';
import { BlogLabelsSidebar } from './BlogLabelsSidebar';
import { BlogPopularPosts } from './BlogPopularPosts';
import { BlogPostCard } from './BlogPostCard';
import {
    BlogPostCardSkeleton,
    BlogPopularPostsSkeleton,
    BlogLabelsSidebarSkeleton,
} from './BlogPostCardSkeleton';
import type { BlogIndexPageProps } from '@/features/blog/types';

export function BlogIndexPage({
    posts,
    categories,
    tags,
    filters,
    activeFilterLabels,
    popularPosts,
}: BlogIndexPageProps) {
    const hasFilters =
        filters.search !== '' || filters.category !== '' || filters.tag !== '';

    const clearFilter = (key: string) => {
        const newQuery: Record<string, string> = {};

        if (filters.search && key !== 'search') {
            newQuery.search = filters.search;
        }

        if (filters.category && key !== 'category') {
            newQuery.category = filters.category;
        }

        if (filters.tag && key !== 'tag') {
            newQuery.tag = filters.tag;
        }

        router.get(blog.index.url(), newQuery, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <PageLayout
            title="Artikel"
            metaDescription="Kumpulan artikel pilihan dari Ruang Baca Informatika Universitas Malikussaleh."
            maxWidth="7xl"
            className="pt-0 pb-16"
            showDesktopNoticeInContent={false}
            header={
                <div className="relative -mt-20 overflow-hidden bg-background sm:-mt-28 md:-mt-24">
                    <div className="relative mx-auto max-w-7xl px-4 pt-24 pb-8 sm:px-6 sm:pt-30 lg:px-8">
                        {/* Breadcrumbs */}
                        <div className="-mx-4 mb-6 hidden border-y border-border/60 bg-muted/5 px-4 py-3 sm:-mx-6 sm:flex sm:items-center sm:px-6 lg:-mx-8 lg:px-8">
                            <Breadcrumbs
                                breadcrumbs={[
                                    { title: 'Beranda', href: '/' },
                                    {
                                        title: 'Artikel',
                                        href: blog.index.url(),
                                    },
                                ]}
                            />
                        </div>

                        {/* Title Section */}
                        <div className="pt-4 pb-2 sm:pt-0">
                            <h1 className="text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">
                                Artikel
                            </h1>
                        </div>
                    </div>
                </div>
            }
        >
            {/* Active filter pills */}
            {hasFilters && activeFilterLabels.length > 0 && (
                <div className="mb-4 flex flex-wrap items-center gap-2">
                    <span className="text-sm text-muted-foreground">
                        Filter aktif:
                    </span>
                    {activeFilterLabels.map(({ key, label }) => (
                        <span
                            key={key}
                            className="inline-flex items-center gap-1 rounded-full border border-primary/30 bg-primary/8 px-3 py-1 text-xs font-medium text-primary"
                        >
                            {label}
                            <button
                                type="button"
                                aria-label={`Hapus filter ${label}`}
                                onClick={() => clearFilter(key)}
                                className="ml-0.5 rounded-full p-0.5 transition-colors hover:bg-primary/20"
                            >
                                <X className="size-3" />
                            </button>
                        </span>
                    ))}
                    <button
                        type="button"
                        onClick={() =>
                            router.get(blog.index.url(), {}, { replace: true })
                        }
                        className="text-xs text-muted-foreground underline-offset-2 hover:text-foreground hover:underline"
                    >
                        Hapus semua
                    </button>
                </div>
            )}

            {/* =====================================================
                MAIN TWO-COLUMN LAYOUT  (content + sidebar)
            ===================================================== */}
            <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem]">
                {/* ─── LEFT COLUMN ─── */}
                <div className="min-w-0 space-y-4">
                    <Deferred
                        data="posts"
                        fallback={
                            <div className="space-y-6">
                                {!hasFilters && (
                                    <BlogPostCardSkeleton variant="featured" />
                                )}
                                <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                                    {Array.from({ length: 6 }).map((_, idx) => (
                                        <BlogPostCardSkeleton key={idx} />
                                    ))}
                                </div>
                            </div>
                        }
                    >
                        {(() => {
                            if (!posts) {
                                return null;
                            }

                            const publishedPosts = posts.data;
                            const featuredPost = publishedPosts[0] ?? null;
                            const remainingPosts = hasFilters
                                ? publishedPosts
                                : publishedPosts.slice(1);

                            return (
                                <>
                                    {/* 1. HERO FEATURED POST (only when not filtering) */}
                                    {!hasFilters && featuredPost && (
                                        <section>
                                            <BlogFeaturedPost
                                                post={featuredPost}
                                            />
                                        </section>
                                    )}

                                    {/* 2. PINNED / FEATURED single-card (1st article when filtering) */}
                                    {hasFilters && publishedPosts[0] && (
                                        <h2 className="text-lg font-bold">
                                            Hasil Pencarian
                                        </h2>
                                    )}

                                    {/* 3. LATEST POSTS GRID */}
                                    {remainingPosts.length > 0 ? (
                                        <section className="space-y-4">
                                            {!hasFilters && (
                                                <div className="mb-5 flex items-center justify-between">
                                                    <h2 className="text-lg font-bold text-foreground">
                                                        Artikel Terbaru
                                                    </h2>
                                                </div>
                                            )}

                                            <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                                                {remainingPosts.map((post) => (
                                                    <BlogPostCard
                                                        key={post.id}
                                                        post={post}
                                                    />
                                                ))}
                                            </div>
                                        </section>
                                    ) : (
                                        !featuredPost && (
                                            <div className="rounded-2xl border border-dashed border-border/70 bg-card px-6 py-14 text-center">
                                                <p className="text-lg font-semibold">
                                                    Artikel tidak ditemukan
                                                </p>
                                                <p className="mt-2 text-sm text-muted-foreground">
                                                    Coba gunakan kata kunci atau
                                                    filter yang berbeda.
                                                </p>
                                                {hasFilters && (
                                                    <Link
                                                        href={blog.index.url()}
                                                        className="mt-4 inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline"
                                                    >
                                                        Lihat semua artikel
                                                    </Link>
                                                )}
                                            </div>
                                        )
                                    )}

                                    {/* 4. PAGINATION */}
                                    {posts.last_page > 1 && (
                                        <CatalogPagination
                                            data={posts}
                                            resourceName="artikel"
                                        />
                                    )}
                                </>
                            );
                        })()}
                    </Deferred>
                </div>

                {/* ─── RIGHT SIDEBAR ─── */}
                <aside className="space-y-6">
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
                                activeCategory={filters.category}
                                activeTag={filters.tag}
                            />
                        )}
                    </Deferred>
                </aside>
            </div>
        </PageLayout>
    );
}
