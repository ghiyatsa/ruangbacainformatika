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
import type { PaginatedSkripsis } from '@/features/skripsi/types';

interface SkripsiPaginationProps {
    skripsis: PaginatedSkripsis;
}

export function SkripsiPagination({ skripsis }: SkripsiPaginationProps) {
    if (skripsis.last_page <= 1) {
        return null;
    }

    const current = skripsis.current_page;
    const last = skripsis.last_page;

    // Filter out "Previous" and "Next" links from the links array
    const pageLinks = skripsis.links.filter(
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
                    {skripsis.from ?? 0}–{skripsis.to ?? 0}
                </span>{' '}
                dari{' '}
                <span className="font-semibold text-foreground">
                    {(skripsis.total ?? 0).toLocaleString('id-ID')}
                </span>{' '}
                skripsi
            </p>

            <Pagination className="mx-0 w-auto">
                <PaginationContent>
                    <PaginationItem>
                        <PaginationLink
                            href={skripsis.links[0]?.url ?? '#'}
                            disabled={current === 1}
                            aria-label="Halaman pertama"
                            size="icon"
                        >
                            <ChevronsLeft className="size-4" />
                        </PaginationLink>
                    </PaginationItem>

                    <PaginationItem>
                        <PaginationPrevious
                            href={skripsis.prev_page_url ?? '#'}
                            disabled={!skripsis.prev_page_url}
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
                            href={skripsis.next_page_url ?? '#'}
                            disabled={!skripsis.next_page_url}
                        />
                    </PaginationItem>

                    <PaginationItem>
                        <PaginationLink
                            href={
                                skripsis.links[skripsis.links.length - 1]
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
