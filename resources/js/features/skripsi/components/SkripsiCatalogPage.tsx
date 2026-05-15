import { CatalogPage } from '@/components/catalog/CatalogPage';
import { MobileProgressivePagination } from '@/components/catalog/MobileProgressivePagination';
import { router } from '@inertiajs/react';
import { SkripsiCatalogFilters } from '@/features/skripsi/components/SkripsiCatalogFilters';
import { SkripsiCatalogResults } from '@/features/skripsi/components/SkripsiCatalogResults';
import type { SkripsiCatalogPageProps } from '@/features/skripsi/types';
import skripsiRoute from '@/routes/skripsi';

export default function SkripsiCatalogPage({
    filters,
    years,
    total,
    skripsis,
}: SkripsiCatalogPageProps) {
    function clearAllFilters(): void {
        router.get(
            skripsiRoute.index.url(),
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
        }

        router.get(skripsiRoute.index.url(), next, {
            preserveScroll: true,
            replace: true,
        });
    }

    return (
        <CatalogPage
            title="Katalog Skripsi"
            metaDescription="Telusuri koleksi skripsi mahasiswa Teknik Informatika Universitas Malikussaleh sebagai referensi topik dan penelitian."
            resourceName="skripsi"
            breadcrumbLabel="Katalog Skripsi"
            totalCount={total}
            paginationData={skripsis}
            filters={filters}
            onClearFilters={clearAllFilters}
            onRemoveFilter={removeFilter}
            paginationVisibility="desktop-only"
            filtersPanel={
                <SkripsiCatalogFilters
                    filters={filters}
                    years={years}
                    total={total}
                />
            }
            deferredData="skripsis"
        >
            <SkripsiCatalogResults skripsis={skripsis} />
            <MobileProgressivePagination
                data={skripsis}
                propKey="skripsis"
                resourceLabel="skripsi"
                resetKey={JSON.stringify(filters)}
            />
        </CatalogPage>
    );
}
