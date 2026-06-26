import { Deferred, router } from '@inertiajs/react';
import { lazy, Suspense, useState } from 'react';
import BookCardSkeleton from '@/features/books/components/BookCardSkeleton';
import BookCatalogFiltersSkeleton from '@/features/books/components/BookCatalogFiltersSkeleton';
import { BookCatalogResults } from '@/features/books/components/BookCatalogResults';
import { CatalogMobilePagination } from '@/features/books/components/CatalogMobilePagination';
import { CatalogPage } from '@/features/books/components/CatalogPage';
import booksRoute from '@/routes/books';
import type { BookCatalogPageProps, ViewMode } from '@/features/books/types';

const LazyBookCatalogFilters = lazy(async () => {
    const { BookCatalogFilters } = await import(
        './BookCatalogFilters'
    );

    return { default: BookCatalogFilters };
});

export default function BookCatalogPage({
    filters,
    stats,
    categories,
    authors,
    publishers,
    years,
    books,
}: BookCatalogPageProps) {
    const [viewMode, setViewMode] = useState<ViewMode>('grid');
    const activeCategoryLabel =
        categories?.find((category) => category.slug === filters.category)
            ?.name ?? undefined;

    function clearAllFilters(): void {
        router.get(
            booksRoute.index.url(),
            {},
            { preserveScroll: true, replace: true },
        );
    }

    function removeFilter(key: string): void {
        const next = { ...filters };

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
            totalCount={stats.booksCount ?? 0}
            paginationData={books}
            filters={filters}
            filterLabels={{
                category: activeCategoryLabel,
                author: filters.author ? (authors?.find(a => a.slug === filters.author)?.name ?? undefined) : undefined,
                publisher: filters.publisher ? (publishers?.find(p => p.slug === filters.publisher)?.name ?? undefined) : undefined,
            }}
            onClearFilters={clearAllFilters}
            onRemoveFilter={removeFilter}
            paginationVisibility="none"
            filtersPanel={
                <Suspense fallback={<BookCatalogFiltersSkeleton />}>
                    <Deferred
                        data={['categories', 'authors', 'publishers', 'years']}
                        fallback={<BookCatalogFiltersSkeleton />}
                    >
                        <LazyBookCatalogFilters
                            filters={filters}
                            stats={stats}
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
                        <div className="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 2xl:grid-cols-6">
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
                completeLabel="Semua judul buku telah ditampilkan."
                loadingFallback={
                    <div
                        className={
                            viewMode === 'list'
                                ? 'grid w-full grid-cols-1 gap-3 lg:grid-cols-2'
                                : 'grid w-full grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 2xl:grid-cols-6'
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
                loadingFallback={
                    <div
                        className={
                            viewMode === 'list'
                                ? 'grid w-full grid-cols-1 gap-3'
                                : 'grid w-full grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 2xl:grid-cols-6'
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
