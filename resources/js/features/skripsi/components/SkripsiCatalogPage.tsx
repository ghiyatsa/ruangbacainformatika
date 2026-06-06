import { router } from '@inertiajs/react';
import { CatalogPage } from '@/components/catalog/CatalogPage';
import { MobileProgressivePagination } from '@/components/catalog/MobileProgressivePagination';
import { CatalogResourceCardSkeleton } from '@/components/resource/CatalogResourceCardSkeleton';
import { SkripsiCatalogFilters } from '@/features/skripsi/components/SkripsiCatalogFilters';
import { SkripsiCatalogResults } from '@/features/skripsi/components/SkripsiCatalogResults';
import skripsiRoute from '@/routes/skripsi';
import type { SkripsiCatalogPageProps } from '@/features/skripsi/types';

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
            title="Skripsi"
            metaDescription="Lihat daftar skripsi mahasiswa Teknik Informatika Universitas Malikussaleh."
            resourceName="skripsi"
            breadcrumbLabel="Skripsi"
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
            loadingFallback={
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {Array.from({ length: 10 }).map((_, index) => (
                        <CatalogResourceCardSkeleton key={index} />
                    ))}
                </div>
            }
        >
            <SkripsiCatalogResults skripsis={skripsis} />
            <MobileProgressivePagination
                key={JSON.stringify(filters)}
                data={skripsis}
                propKey="skripsis"
                resourceLabel="skripsi"
            />
        </CatalogPage>
    );
}
