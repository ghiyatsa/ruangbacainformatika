import { router } from '@inertiajs/react';
import { useState } from 'react';
import { CatalogPage } from '@/components/catalog/CatalogPage';
import { MobileProgressivePagination } from '@/components/catalog/MobileProgressivePagination';
import BookCardSkeleton from '@/features/books/components/BookCardSkeleton';
import { BookCatalogFilters } from '@/features/books/components/BookCatalogFilters';
import { BookCatalogResults } from '@/features/books/components/BookCatalogResults';
import booksRoute from '@/routes/books';
import type { BookCatalogPageProps, ViewMode } from '@/features/books/types';

export default function BookCatalogPage({
    filters,
    stats,
    categories,
    years,
    books,
}: BookCatalogPageProps) {
    const [viewMode, setViewMode] = useState<ViewMode>('grid');

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
            title="Katalog Buku"
            metaDescription="Jelajahi katalog buku Ruang Baca Teknik Informatika Universitas Malikussaleh untuk referensi kuliah, riset, dan pembelajaran."
            resourceName="judul buku"
            breadcrumbLabel="Katalog Buku"
            totalCount={stats.booksCount ?? 0}
            paginationData={books}
            filters={filters}
            onClearFilters={clearAllFilters}
            onRemoveFilter={removeFilter}
            paginationVisibility="desktop-only"
            filtersPanel={
                <BookCatalogFilters
                    filters={filters}
                    stats={stats}
                    categories={categories}
                    years={years}
                    viewMode={viewMode}
                    onViewModeChange={setViewMode}
                />
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
            <MobileProgressivePagination
                key={JSON.stringify(filters)}
                data={books}
                propKey="books"
                resourceLabel="judul buku"
            />
        </CatalogPage>
    );
}
