import { Deferred, router } from '@inertiajs/react';
import { Search, X } from 'lucide-react';
import { CatalogPageLayout } from '@/components/catalog/CatalogPageLayout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { InternshipReportCatalogPageProps } from '@/features/internship-report/types';
import internshipReportRoute from '@/routes/internship-reports';
import { InternshipReportCatalogFilters } from './InternshipReportCatalogFilters';
import { InternshipReportCatalogHeader } from './InternshipReportCatalogHeader';
import { InternshipReportCatalogResults } from './InternshipReportCatalogResults';
import { InternshipReportGridSkeleton } from './InternshipReportGridSkeleton';

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

    return (
        <CatalogPageLayout
            title="Katalog Laporan KP"
            header={<InternshipReportCatalogHeader total={total} />}
        >
            <div className="flex flex-col gap-6">
                <InternshipReportCatalogFilters
                    filters={filters}
                    years={years}
                    total={total}
                />

                {/* Active Search Badge */}
                {filters.search && (
                    <div className="flex items-center gap-2">
                        <Badge
                            variant="secondary"
                            className="gap-1.5 py-1.5 pr-2 pl-2.5"
                        >
                            <Search className="size-3 text-muted-foreground" />
                            <span className="text-muted-foreground">
                                Hasil pencarian:
                            </span>
                            &ldquo;{filters.search}&rdquo;
                            <button
                                onClick={clearAllFilters}
                                className="ml-1 rounded-full p-0.5 transition-colors hover:bg-muted"
                            >
                                <X className="size-3" />
                            </button>
                        </Badge>
                        <Button
                            variant="ghost"
                            size="sm"
                            className="h-8 text-xs text-muted-foreground"
                            onClick={clearAllFilters}
                        >
                            Hapus semua filter
                        </Button>
                    </div>
                )}
            </div>

            {/* Results */}
            <Deferred
                data="reports"
                fallback={<InternshipReportGridSkeleton />}
            >
                <InternshipReportCatalogResults reports={reports} />
            </Deferred>
        </CatalogPageLayout>
    );
}
