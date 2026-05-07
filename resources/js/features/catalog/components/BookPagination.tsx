import { router } from '@inertiajs/react';
import {
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
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

    function navigateTo(url: string | null): void {
        if (!url) {
            return;
        }

        router.visit(url, {
            preserveScroll: false,
            onSuccess: () => window.scrollTo({ top: 0, behavior: 'smooth' }),
        });
    }

    const pageLinks = books.links.filter((l) => !isNaN(Number(l.label)));

    const current = books.current_page;
    const last = books.last_page;
    const delta = 2;
    const rangeStart = Math.max(1, current - delta);
    const rangeEnd = Math.min(last, current + delta);

    const visiblePageLinks = pageLinks.filter((l) => {
        const n = Number(l.label);

        return n >= rangeStart && n <= rangeEnd;
    });

    const showFirst = rangeStart > 1;
    const showLast = rangeEnd < last;
    const showStartEllipsis = rangeStart > 2;
    const showEndEllipsis = rangeEnd < last - 1;

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

            <div className="flex items-center gap-1">
                <Button
                    variant="ghost"
                    size="icon"
                    className="size-8"
                    disabled={current === 1}
                    onClick={() => navigateTo(books.links[0]?.url ?? null)}
                    aria-label="Halaman pertama"
                >
                    <ChevronsLeft className="size-4" />
                </Button>

                <Button
                    variant="ghost"
                    size="icon"
                    className="size-8"
                    disabled={!books.prev_page_url}
                    onClick={() => navigateTo(books.prev_page_url)}
                    aria-label="Halaman sebelumnya"
                >
                    <ChevronLeft className="size-4" />
                </Button>

                {showFirst && (
                    <>
                        <Button
                            variant="ghost"
                            size="icon"
                            className="size-8 text-sm"
                            onClick={() =>
                                navigateTo(
                                    pageLinks.find((l) => l.label === '1')
                                        ?.url ?? null,
                                )
                            }
                        >
                            1
                        </Button>
                        {showStartEllipsis && (
                            <span className="px-1 text-sm text-muted-foreground">
                                …
                            </span>
                        )}
                    </>
                )}

                {visiblePageLinks.map((link) => (
                    <Button
                        key={link.label}
                        variant={link.active ? 'default' : 'ghost'}
                        size="icon"
                        className="size-8 text-sm"
                        onClick={() => navigateTo(link.url)}
                        disabled={link.active}
                        aria-current={link.active ? 'page' : undefined}
                    >
                        {link.label}
                    </Button>
                ))}

                {showLast && (
                    <>
                        {showEndEllipsis && (
                            <span className="px-1 text-sm text-muted-foreground">
                                …
                            </span>
                        )}
                        <Button
                            variant="ghost"
                            size="icon"
                            className="size-8 text-sm"
                            onClick={() =>
                                navigateTo(
                                    pageLinks.find(
                                        (l) => l.label === String(last),
                                    )?.url ?? null,
                                )
                            }
                        >
                            {last}
                        </Button>
                    </>
                )}

                <Button
                    variant="ghost"
                    size="icon"
                    className="size-8"
                    disabled={!books.next_page_url}
                    onClick={() => navigateTo(books.next_page_url)}
                    aria-label="Halaman berikutnya"
                >
                    <ChevronRight className="size-4" />
                </Button>

                <Button
                    variant="ghost"
                    size="icon"
                    className="size-8"
                    disabled={current === last}
                    onClick={() =>
                        navigateTo(
                            books.links[books.links.length - 1]?.url ?? null,
                        )
                    }
                    aria-label="Halaman terakhir"
                >
                    <ChevronsRight className="size-4" />
                </Button>
            </div>
        </div>
    );
}
