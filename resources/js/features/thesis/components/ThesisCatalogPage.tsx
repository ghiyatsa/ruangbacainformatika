import { router } from '@inertiajs/react';
import { CatalogPage } from '@/components/catalog/CatalogPage';
import { MobileProgressivePagination } from '@/components/catalog/MobileProgressivePagination';
import { CatalogResourceCardSkeleton } from '@/components/resource/CatalogResourceCardSkeleton';
import { ThesisCatalogFilters } from '@/features/thesis/components/ThesisCatalogFilters';
import { ThesisCatalogResults } from '@/features/thesis/components/ThesisCatalogResults';
import thesisRoute from '@/routes/thesis';
import type { ThesisCatalogPageProps } from '@/features/thesis/types';

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
            metaDescription="Lihat daftar tesis Teknik Informatika Universitas Malikussaleh."
            resourceName="tesis"
            breadcrumbLabel="Katalog Tesis"
            totalCount={total}
            paginationData={theses}
            filters={filters}
            onClearFilters={clearAllFilters}
            onRemoveFilter={removeFilter}
            paginationVisibility="desktop-only"
            filtersPanel={
                <ThesisCatalogFilters
                    filters={filters}
                    years={years}
                    total={total}
                />
            }
            deferredData="theses"
            loadingFallback={
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
                    {Array.from({ length: 10 }).map((_, index) => (
                        <CatalogResourceCardSkeleton key={index} />
                    ))}
                </div>
            }
        >
            <ThesisCatalogResults theses={theses} />
            <MobileProgressivePagination
                key={JSON.stringify(filters)}
                data={theses}
                propKey="theses"
                resourceLabel="tesis"
            />
        </CatalogPage>
    );
}
