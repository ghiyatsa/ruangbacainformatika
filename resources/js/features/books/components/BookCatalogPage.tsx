import { router } from '@inertiajs/react';
import { useState } from 'react';
import { CatalogPage } from '@/components/catalog/CatalogPage';
import { BookCatalogFilters } from '@/features/books/components/BookCatalogFilters';
import { BookCatalogResults } from '@/features/books/components/BookCatalogResults';
import type { BookCatalogPageProps, ViewMode } from '@/features/books/types';
import booksRoute from '@/routes/books';

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
        >
            <BookCatalogResults books={books} viewMode={viewMode} />
        </CatalogPage>
    );
}
