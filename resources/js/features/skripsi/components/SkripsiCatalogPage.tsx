import { router } from '@inertiajs/react';
import { CatalogPage } from '@/components/catalog/CatalogPage';
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
            resourceName="skripsi"
            breadcrumbLabel="Katalog Skripsi"
            totalCount={total}
            paginationData={skripsis}
            filters={filters}
            onClearFilters={clearAllFilters}
            onRemoveFilter={removeFilter}
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
        </CatalogPage>
    );
}
