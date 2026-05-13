import { router } from '@inertiajs/react';
import { CatalogPage } from '@/components/catalog/CatalogPage';
import { ThesisCatalogFilters } from '@/features/thesis/components/ThesisCatalogFilters';
import { ThesisCatalogResults } from '@/features/thesis/components/ThesisCatalogResults';
import type { ThesisCatalogPageProps } from '@/features/thesis/types';
import thesisRoute from '@/routes/thesis';

export default function ThesisCatalogPage({
    filters,
    years,
    total,
    theses,
}: ThesisCatalogPageProps) {
    function clearAllFilters(): void {
        router.get(
            thesisRoute.index.url(),
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

        router.get(thesisRoute.index.url(), next, {
            preserveScroll: true,
            replace: true,
        });
    }

    return (
        <CatalogPage
            title="Katalog Tesis"
            metaDescription="Lihat koleksi tesis Teknik Informatika Universitas Malikussaleh untuk kebutuhan studi lanjut dan pengayaan riset."
            resourceName="tesis"
            breadcrumbLabel="Katalog Tesis"
            totalCount={total}
            paginationData={theses}
            filters={filters}
            onClearFilters={clearAllFilters}
            onRemoveFilter={removeFilter}
            filtersPanel={
                <ThesisCatalogFilters
                    filters={filters}
                    years={years}
                    total={total}
                />
            }
            deferredData="theses"
        >
            <ThesisCatalogResults theses={theses} />
        </CatalogPage>
    );
}
