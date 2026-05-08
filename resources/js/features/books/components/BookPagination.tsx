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
import type { PaginatedBooks } from '@/features/welcome/types';

interface BookPaginationProps {
    books: PaginatedBooks;
}

/**
 * Shared pagination control used by the catalog and category pages.
 * Renders nothing when there is only one page.
 */
export function BookPagination({ books }: BookPaginationProps) {
    if (books.last_page <= 1) {
        return null;
    }

    const current = books.current_page;
    const last = books.last_page;

    // Filter out "Previous" and "Next" links from the links array
    const pageLinks = books.links.filter((link) => !isNaN(Number(link.label)));

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
                    {books.from}–{books.to}
                </span>{' '}
                dari{' '}
                <span className="font-semibold text-foreground">
                    {books.total.toLocaleString('id-ID')}
                </span>{' '}
                buku
            </p>

            <Pagination className="mx-0 w-auto">
                <PaginationContent>
                    <PaginationItem>
                        <PaginationLink
                            href={books.links[0]?.url ?? '#'}
                            disabled={current === 1}
                            aria-label="Halaman pertama"
                            size="icon"
                        >
                            <ChevronsLeft className="size-4" />
                        </PaginationLink>
                    </PaginationItem>

                    <PaginationItem>
                        <PaginationPrevious
                            href={books.prev_page_url ?? '#'}
                            disabled={!books.prev_page_url}
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
                            href={books.next_page_url ?? '#'}
                            disabled={!books.next_page_url}
                        />
                    </PaginationItem>

                    <PaginationItem>
                        <PaginationLink
                            href={
                                books.links[books.links.length - 1]?.url ?? '#'
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
