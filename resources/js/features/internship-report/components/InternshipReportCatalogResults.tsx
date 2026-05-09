import { BookMarked } from 'lucide-react';
import {
    Empty,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';
import { Separator } from '@/components/ui/separator';
import type { PaginatedInternshipReports } from '@/features/internship-report/types';
import InternshipReportCard from './InternshipReportCard';
import { InternshipReportPagination } from './InternshipReportPagination';

interface InternshipReportCatalogResultsProps {
    reports: PaginatedInternshipReports;
}

export function InternshipReportCatalogResults({
    reports,
}: InternshipReportCatalogResultsProps) {
    const total = reports.total ?? 0;

    return (
        <div className="flex flex-col gap-6">
            {reports.data && reports.data.length > 0 ? (
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {reports.data.map((r) => (
                        <InternshipReportCard key={r.id} report={r} />
                    ))}
                </div>
            ) : (
                <Empty className="border-2 py-20">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <BookMarked />
                        </EmptyMedia>
                        <EmptyTitle>Laporan KP tidak ditemukan</EmptyTitle>
                        <EmptyDescription>
                            Coba kata kunci lain atau hapus filter yang aktif.
                        </EmptyDescription>
                    </EmptyHeader>
                </Empty>
            )}

            {total > 0 && (
                <>
                    <Separator />
                    <InternshipReportPagination reports={reports} />
                </>
            )}
        </div>
    );
}
