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
import type { PaginationData } from '@/types/pagination';

interface ResourcePaginationProps<T> {
    data: PaginationData<T>;
    resourceName: string;
}

/**
 * Shared pagination control used by all resource catalogs.
 * Renders nothing when there is only one page.
 */
export function ResourcePagination<T>({
    data,
    resourceName,
}: ResourcePaginationProps<T>) {
    if (data.last_page <= 1) {
        return null;
    }

    const current = data.current_page;
    const last = data.last_page;

    // Filter out "Previous" and "Next" links from the links array
    const pageLinks = data.links.filter((link) => !isNaN(Number(link.label)));

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
                    {data.from ?? 0}–{data.to ?? 0}
                </span>{' '}
                dari{' '}
                <span className="font-semibold text-foreground">
                    {(data.total ?? 0).toLocaleString('id-ID')}
                </span>{' '}
                {resourceName}
            </p>

            <Pagination className="mx-0 w-auto">
                <PaginationContent>
                    <PaginationItem>
                        <PaginationLink
                            href={data.first_page_url ?? '#'}
                            disabled={current === 1}
                            aria-label="Halaman pertama"
                            size="icon"
                        >
                            <ChevronsLeft className="size-4" />
                        </PaginationLink>
                    </PaginationItem>

                    <PaginationItem>
                        <PaginationPrevious
                            href={data.prev_page_url ?? '#'}
                            disabled={!data.prev_page_url}
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
                            href={data.next_page_url ?? '#'}
                            disabled={!data.next_page_url}
                        />
                    </PaginationItem>

                    <PaginationItem>
                        <PaginationLink
                            href={data.last_page_url ?? '#'}
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
