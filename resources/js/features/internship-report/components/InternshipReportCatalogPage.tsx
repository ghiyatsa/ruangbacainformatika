import { router } from '@inertiajs/react';
import { KtiCardSkeleton } from '@/components/kti/KtiCardSkeleton';
import { CatalogMobilePagination } from '@/features/books/components/CatalogMobilePagination';
import { CatalogPage } from '@/features/books/components/CatalogPage';
import internshipReportRoute from '@/routes/internship-reports';
import { InternshipReportCatalogFilters } from './InternshipReportCatalogFilters';
import { InternshipReportCatalogResults } from './InternshipReportCatalogResults';
import type { InternshipReportCatalogPageProps } from '@/features/internship-report/types';

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
                <InternshipReportCatalogFilters
                    filters={filters}
                    years={years}
                    total={total}
                />
            }
            deferredData="reports"
            loadingFallback={
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
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
            />
        </CatalogPage>
    );
}

