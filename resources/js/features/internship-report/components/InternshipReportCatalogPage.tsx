import { router } from '@inertiajs/react';
import { lazy, Suspense } from 'react';
import { KtiCardSkeleton } from '@/components/kti/KtiCardSkeleton';
import { KtiCatalogFiltersSkeleton } from '@/components/kti/KtiCatalogFiltersSkeleton';
import { CatalogMobilePagination } from '@/features/books/components/CatalogMobilePagination';
import { CatalogPage } from '@/features/books/components/CatalogPage';
import internshipReportRoute from '@/routes/internship-reports';
import { InternshipReportCatalogResults } from './InternshipReportCatalogResults';
import type { InternshipReportCatalogPageProps } from '@/features/internship-report/types';

const LazyInternshipReportCatalogFilters = lazy(async () => {
    const { InternshipReportCatalogFilters } = await import(
        './InternshipReportCatalogFilters'
    );

    return { default: InternshipReportCatalogFilters };
});

export default function InternshipReportCatalogPage({
    filters,
    years,
    total,
    reports,
}: InternshipReportCatalogPageProps) {
    function clearAllFilters(): void {
        router.get(
            internshipReportRoute.index.url(),
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

        router.get(internshipReportRoute.index.url(), next, {
            preserveScroll: true,
            replace: true,
        });
    }

    return (
        <CatalogPage
            title="Laporan KP"
            metaDescription="Lihat daftar laporan kerja praktik mahasiswa Teknik Informatika Universitas Malikussaleh."
            resourceName="laporan"
            breadcrumbLabel="Laporan KP"
            totalCount={total}
            paginationData={reports}
            filters={filters}
            onClearFilters={clearAllFilters}
            onRemoveFilter={removeFilter}
            paginationVisibility="desktop-only"
            filtersPanel={
                <Suspense fallback={<KtiCatalogFiltersSkeleton />}>
                    <LazyInternshipReportCatalogFilters
                        filters={filters}
                        years={years}
                        total={total}
                    />
                </Suspense>
            }
            deferredData="reports"
            loadingFallback={
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-[repeat(auto-fill,minmax(200px,1fr))]">
                    {Array.from({ length: 10 }).map((_, index) => (
                        <KtiCardSkeleton key={index} />
                    ))}
                </div>
            }
        >
            <InternshipReportCatalogResults reports={reports} />
            <CatalogMobilePagination
                key={JSON.stringify(filters)}
                data={reports}
                propKey="reports"
                resourceLabel="laporan"
                loadingFallback={
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-[repeat(auto-fill,minmax(200px,1fr))]" aria-hidden="true">
                        {Array.from({ length: 5 }).map((_, index) => (
                            <KtiCardSkeleton key={`load-more-${index}`} />
                        ))}
                    </div>
                }
            />
        </CatalogPage>
    );
}

