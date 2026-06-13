import { Skeleton } from '@/components/ui/skeleton';

/**
 * Skeleton placeholder for KTI catalog filter toolbars (skripsi, tesis, laporan KP).
 * Matches the layout of AcademicWorkCatalogFilters / InternshipReportCatalogFilters
 * – a result-count badge + a year-select trigger.
 */
export function KtiCatalogFiltersSkeleton() {
    return (
        <div className="flex items-center gap-3" aria-hidden="true">
            {/* Result count badge */}
            <Skeleton className="hidden h-9 w-20 rounded-lg lg:block" />
            {/* Year select */}
            <Skeleton className="h-10 w-full rounded-lg sm:w-36" />
        </div>
    );
}

export default KtiCatalogFiltersSkeleton;
