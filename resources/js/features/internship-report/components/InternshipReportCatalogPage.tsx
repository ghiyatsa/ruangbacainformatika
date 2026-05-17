import { router } from '@inertiajs/react';
import { CatalogPage } from '@/components/catalog/CatalogPage';
import { MobileProgressivePagination } from '@/components/catalog/MobileProgressivePagination';
import { CatalogResourceCardSkeleton } from '@/components/resource/CatalogResourceCardSkeleton';
import type { InternshipReportCatalogPageProps } from '@/features/internship-report/types';
import internshipReportRoute from '@/routes/internship-reports';
import { InternshipReportCatalogFilters } from './InternshipReportCatalogFilters';
import { InternshipReportCatalogResults } from './InternshipReportCatalogResults';

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
            title="Katalog Laporan KP"
            metaDescription="Akses koleksi laporan kerja praktik mahasiswa Teknik Informatika Universitas Malikussaleh sebagai referensi pengalaman lapangan."
            resourceName="laporan"
            breadcrumbLabel="Katalog Laporan KP"
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
                        <CatalogResourceCardSkeleton key={index} />
                    ))}
                </div>
            }
        >
            <InternshipReportCatalogResults reports={reports} />
            <MobileProgressivePagination
                key={JSON.stringify(filters)}
                data={reports}
                propKey="reports"
                resourceLabel="laporan"
            />
        </CatalogPage>
    );
}
