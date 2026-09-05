import { Deferred, router } from '@inertiajs/react';
import { lazy, Suspense } from 'react';
import BookCardSkeleton from '@/features/books/components/BookCardSkeleton';
import BookCatalogFiltersSkeleton from '@/features/books/components/BookCatalogFiltersSkeleton';
import { BookCatalogResults } from '@/features/books/components/BookCatalogResults';
import { CatalogMobilePagination } from '@/features/books/components/CatalogMobilePagination';
import { CatalogPage } from '@/features/books/components/CatalogPage';
import { useBookCollectionViewMode } from '@/hooks/use-book-collection-view-mode';
import booksRoute from '@/routes/books';
import type { BookCatalogPageProps } from '@/features/books/types';

const LazyBookCatalogFilters = lazy(async () => {
    const { BookCatalogFilters } = await import('./BookCatalogFilters');

    return { default: BookCatalogFilters };
});

export default function BookCatalogPage({
    filters = {} as BookCatalogPageProps['filters'],
    stats = {} as BookCatalogPageProps['stats'],
    categories = [],
    authors = [],
    publishers = [],
    years = [],
    books,
}: BookCatalogPageProps) {
    const [viewMode, setViewMode] = useBookCollectionViewMode();
    const safeFilters = filters || ({} as BookCatalogPageProps['filters']);
    const activeCategoryLabel =
        categories?.find((category) => category.slug === safeFilters.category)
            ?.name ?? undefined;

    function clearAllFilters(): void {
        router.get(
            booksRoute.index.url(),
            {},
            { preserveScroll: true, replace: true },
        );
    }

    function removeFilter(key: string): void {
        const next = { ...safeFilters };

        if (key === 'search') {
            next.search = '';
        } else if (key === 'category') {
            next.category = '';
        } else if (key === 'author') {
            next.author = '';
        } else if (key === 'publisher') {
            next.publisher = '';
        } else if (key === 'year') {
            next.year = null;
        } else if (key === 'featured') {
            next.featured = false;
        } else if (key === 'availability') {
            next.availability = false;
        }

        router.get(booksRoute.index.url(), next, {
            preserveScroll: true,
            replace: true,
        });
    }

    return (
        <CatalogPage
            title="Buku"
            metaDescription="Lihat daftar buku Ruang Baca Teknik Informatika Universitas Malikussaleh."
            resourceName="judul buku"
            breadcrumbLabel="Buku"
            totalCount={stats?.booksCount ?? 0}
            paginationData={books}
            paginationVisibility="none"
            filters={safeFilters}
            filterLabels={{
                category: activeCategoryLabel,
                author: safeFilters.author
                    ? (authors?.find((a) => a.slug === safeFilters.author)
                          ?.name ?? undefined)
                    : undefined,
                publisher: safeFilters.publisher
                    ? (publishers?.find((p) => p.slug === safeFilters.publisher)
                          ?.name ?? undefined)
                    : undefined,
            }}
            onClearFilters={clearAllFilters}
            onRemoveFilter={removeFilter}
            filtersPanel={
                <Suspense fallback={<BookCatalogFiltersSkeleton />}>
                    <Deferred
                        data={['categories', 'authors', 'publishers', 'years']}
                        fallback={<BookCatalogFiltersSkeleton />}
                    >
                        <LazyBookCatalogFilters
                            filters={safeFilters}
                            categories={categories ?? []}
                            authors={authors ?? []}
                            publishers={publishers ?? []}
                            years={years ?? []}
                            viewMode={viewMode}
                            onViewModeChange={setViewMode}
                        />
                    </Deferred>
                </Suspense>
            }
            deferredData="books"
            loadingFallback={
                <div className="flex flex-col gap-6">
                    {viewMode === 'list' ? (
                        <div className="grid grid-cols-1 gap-3 lg:grid-cols-2">
                            {Array.from({ length: 8 }).map((_, index) => (
                                <BookCardSkeleton
                                    key={index}
                                    variant="compact"
                                />
                            ))}
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 gap-3 sm:grid-cols-[repeat(auto-fill,minmax(170px,1fr))] sm:gap-4">
                            {Array.from({ length: 12 }).map((_, index) => (
                                <BookCardSkeleton key={index} />
                            ))}
                        </div>
                    )}
                </div>
            }
        >
            <BookCatalogResults books={books} viewMode={viewMode} />
            <CatalogMobilePagination
                key={`desktop-${JSON.stringify(filters)}`}
                data={books}
                propKey="books"
                resourceLabel="judul buku"
                className="hidden md:block"
                buttonLabel="Tampilkan lebih banyak"
                completeLabel="Semua judul buku telah ditampilkan."
                loadingFallback={
                    <div
                        className={
                            viewMode === 'list'
                                ? 'grid w-full grid-cols-1 gap-3 lg:grid-cols-2'
                                : 'grid w-full grid-cols-1 gap-3 sm:grid-cols-[repeat(auto-fill,minmax(170px,1fr))] sm:gap-4'
                        }
                        aria-hidden="true"
                    >
                        {Array.from({ length: 6 }).map((_, index) => (
                            <BookCardSkeleton
                                key={`desktop-load-more-${index}`}
                                variant={
                                    viewMode === 'list' ? 'compact' : 'grid'
                                }
                            />
                        ))}
                    </div>
                }
            />
            <CatalogMobilePagination
                key={JSON.stringify(filters)}
                data={books}
                propKey="books"
                resourceLabel="judul buku"
                className="md:hidden"
                buttonLabel="Tampilkan lebih banyak"
                loadingFallback={
                    <div
                        className={
                            viewMode === 'list'
                                ? 'grid w-full grid-cols-1 gap-3'
                                : 'grid w-full grid-cols-1 gap-3 sm:grid-cols-[repeat(auto-fill,minmax(170px,1fr))] sm:gap-4'
                        }
                        aria-hidden="true"
                    >
                        {Array.from({ length: 4 }).map((_, index) => (
                            <BookCardSkeleton
                                key={`load-more-${index}`}
                                variant={
                                    viewMode === 'list' ? 'compact' : 'grid'
                                }
                            />
                        ))}
                    </div>
                }
            />
        </CatalogPage>
    );
}
