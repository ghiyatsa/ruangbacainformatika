import { router } from '@inertiajs/react';
import { KtiCardSkeleton } from '@/components/kti/KtiCardSkeleton';
import { AcademicWorkCatalogFilters } from '@/features/academic-works/components/AcademicWorkCatalogFilters';
import { AcademicWorkCatalogResults } from '@/features/academic-works/components/AcademicWorkCatalogResults';
import { CatalogMobilePagination } from '@/features/books/components/CatalogMobilePagination';
import { CatalogPage } from '@/features/books/components/CatalogPage';
import skripsiRoute from '@/routes/skripsi';
import thesisRoute from '@/routes/thesis';
import type { AcademicWorkCatalogPageProps } from '@/features/academic-works/types';

export default function AcademicWorkCatalogPage({
    workType,
    filters,
    years,
    total,
    academicWorks,
}: AcademicWorkCatalogPageProps) {
    const route = workType === 'skripsi' ? skripsiRoute : thesisRoute;
    const label = workType === 'skripsi' ? 'Skripsi' : 'Tesis';
    const dataProp = workType === 'skripsi' ? 'skripsis' : 'theses';
    const description = workType === 'skripsi'
        ? 'Lihat daftar skripsi mahasiswa Teknik Informatika Universitas Malikussaleh.'
        : 'Lihat daftar tesis mahasiswa Teknik Informatika Universitas Malikussaleh.';

    function clearAllFilters(): void {
        router.get(
            route.index.url(),
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

        router.get(route.index.url(), next, {
            preserveScroll: true,
            replace: true,
        });
    }

    return (
        <CatalogPage
            title={label}
            metaDescription={description}
            resourceName={workType}
            breadcrumbLabel={label}
            totalCount={total}
            paginationData={academicWorks}
            filters={filters}
            onClearFilters={clearAllFilters}
            onRemoveFilter={removeFilter}
            paginationVisibility="desktop-only"
            filtersPanel={
                <AcademicWorkCatalogFilters
                    workType={workType}
                    filters={filters}
                    years={years}
                    total={total}
                />
            }
            deferredData={dataProp}
            loadingFallback={
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {Array.from({ length: 10 }).map((_, index) => (
                        <KtiCardSkeleton key={index} />
                    ))}
                </div>
            }
        >
            <AcademicWorkCatalogResults workType={workType} works={academicWorks} />
            <CatalogMobilePagination
                key={JSON.stringify(filters)}
                data={academicWorks}
                propKey={dataProp}
                resourceLabel={workType}
            />
        </CatalogPage>
    );
}
