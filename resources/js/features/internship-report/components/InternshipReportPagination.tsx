import { ChevronsLeft, ChevronsRight } from 'lucide-react';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationLink,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import type { PaginatedInternshipReports } from '@/features/internship-report/types';

interface InternshipReportPaginationProps {
    reports: PaginatedInternshipReports;
}

export function InternshipReportPagination({ reports }: InternshipReportPaginationProps) {
    if (reports.last_page <= 1) {
        return null;
    }

    const current = reports.current_page;
    const last = reports.last_page;

    // Filter out "Previous" and "Next" links from the links array
    const pageLinks = reports.links.filter(
        (link) => !isNaN(Number(link.label)),
    );

    const delta = 1;
    const rangeStart = Math.max(1, current - delta);
    const rangeEnd = Math.min(last, current + delta);

    const visibleLinks = pageLinks.filter((link) => {
        const n = Number(link.label);

        return n >= rangeStart && n <= rangeEnd;
    });

    return (
        <div className="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
            <p className="text-sm text-muted-foreground">
                Menampilkan{' '}
                <span className="font-semibold text-foreground">
                    {reports.from ?? 0}–{reports.to ?? 0}
                </span>{' '}
                dari{' '}
                <span className="font-semibold text-foreground">
                    {(reports.total ?? 0).toLocaleString('id-ID')}
                </span>{' '}
                laporan KP
            </p>

            <Pagination className="mx-0 w-auto">
                <PaginationContent>
                    <PaginationItem>
                        <PaginationLink
                            href={reports.links[0]?.url ?? '#'}
                            disabled={current === 1}
                            aria-label="Halaman pertama"
                            size="icon"
                        >
                            <ChevronsLeft className="size-4" />
                        </PaginationLink>
                    </PaginationItem>

                    <PaginationItem>
                        <PaginationPrevious
                            href={reports.prev_page_url ?? '#'}
                            disabled={!reports.prev_page_url}
                        />
                    </PaginationItem>

                    {rangeStart > 1 && (
                        <>
                            <PaginationItem>
                                <PaginationLink
                                    href={
                                        pageLinks.find((l) => l.label === '1')
                                            ?.url ?? '#'
                                    }
                                >
                                    1
                                </PaginationLink>
                            </PaginationItem>
                            {rangeStart > 2 && (
                                <PaginationItem>
                                    <PaginationEllipsis />
                                </PaginationItem>
                            )}
                        </>
                    )}

                    {visibleLinks.map((link) => (
                        <PaginationItem key={link.label}>
                            <PaginationLink
                                href={link.url ?? '#'}
                                isActive={link.active}
                            >
                                {link.label}
                            </PaginationLink>
                        </PaginationItem>
                    ))}

                    {rangeEnd < last && (
                        <>
                            {rangeEnd < last - 1 && (
                                <PaginationItem>
                                    <PaginationEllipsis />
                                </PaginationItem>
                            )}
                            <PaginationItem>
                                <PaginationLink
                                    href={
                                        pageLinks.find(
                                            (l) => l.label === String(last),
                                        )?.url ?? '#'
                                    }
                                >
                                    {last}
                                </PaginationLink>
                            </PaginationItem>
                        </>
                    )}

                    <PaginationItem>
                        <PaginationNext
                            href={reports.next_page_url ?? '#'}
                            disabled={!reports.next_page_url}
                        />
                    </PaginationItem>

                    <PaginationItem>
                        <PaginationLink
                            href={
                                reports.links[reports.links.length - 1]
                                    ?.url ?? '#'
                            }
                            disabled={current === last}
                            aria-label="Halaman terakhir"
                            size="icon"
                        >
                            <ChevronsRight className="size-4" />
                        </PaginationLink>
                    </PaginationItem>
                </PaginationContent>
            </Pagination>
        </div>
    );
}
