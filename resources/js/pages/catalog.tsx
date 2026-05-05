import { Deferred, Head, router } from '@inertiajs/react';
import {
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
    Library,
    X,
} from 'lucide-react';
import { LayoutGrid, LayoutList } from 'lucide-react';
import { useState } from 'react';
import BookCard from '@/components/catalog/BookCard';
import BookCardSkeleton from '@/components/catalog/BookCardSkeleton';
import BookListItem from '@/components/catalog/BookListItem';
import BookListItemSkeleton from '@/components/catalog/BookListItemSkeleton';
import { AppHeader } from '@/components/layouts/AppHeader';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Empty,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';
import { Separator } from '@/components/ui/separator';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import Footer from '@/components/welcome/Footer';
import type { PaginatedBooks } from '@/components/welcome/types';
import booksRoute from '@/routes/books';

// ─── Types ───────────────────────────────────────────────────────────────────

interface CatalogProps {
    canRegister?: boolean;
    filters: {
        search: string;
        category: string;
    };
    stats: {
        booksCount: number;
        availableItemsCount: number;
        searchResultsCount: number;
    };
    categories: {
        id: number;
        name: string;
        slug: string;
        booksCount: number;
    }[];
    books: PaginatedBooks;
}

// ─── Pagination ───────────────────────────────────────────────────────────────

function Pagination({ books }: { books: PaginatedBooks }) {
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

    // Filter only numeric page links (exclude "Previous" / "Next" labels)
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
            {/* Count summary */}
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

            {/* Page controls */}
            <div className="flex items-center gap-1">
                {/* Jump to first */}
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

                {/* Previous */}
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

                {/* Page 1 if outside range */}
                {showFirst && (
                    <>
                        <Button
                            variant="ghost"
                            size="icon"
                            className="size-8 text-sm"
                            onClick={() =>
                                navigateTo(
                                    pageLinks.find((l) => l.label === '1')?.url ?? null,
                                )
                            }
                        >
                            1
                        </Button>
                        {showStartEllipsis && (
                            <span className="px-1 text-sm text-muted-foreground">…</span>
                        )}
                    </>
                )}

                {/* Numbered pages */}
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

                {/* Last page if outside range */}
                {showLast && (
                    <>
                        {showEndEllipsis && (
                            <span className="px-1 text-sm text-muted-foreground">…</span>
                        )}
                        <Button
                            variant="ghost"
                            size="icon"
                            className="size-8 text-sm"
                            onClick={() =>
                                navigateTo(
                                    pageLinks.find((l) => l.label === String(last))?.url ?? null,
                                )
                            }
                        >
                            {last}
                        </Button>
                    </>
                )}

                {/* Next */}
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

                {/* Jump to last */}
                <Button
                    variant="ghost"
                    size="icon"
                    className="size-8"
                    disabled={current === last}
                    onClick={() =>
                        navigateTo(books.links[books.links.length - 1]?.url ?? null)
                    }
                    aria-label="Halaman terakhir"
                >
                    <ChevronsRight className="size-4" />
                </Button>
            </div>
        </div>
    );
}

// ─── Skeleton ─────────────────────────────────────────────────────────────────

function ResultsSkeleton({ viewMode }: { viewMode: 'grid' | 'list' }) {
    return (
        <div className="flex flex-col gap-4">
            {viewMode === 'list' ? (
                <div className="grid grid-cols-1 gap-3 lg:grid-cols-2">
                    {Array.from({ length: 8 }).map((_, i) => (
                        <div key={i} className="overflow-hidden rounded-xl border bg-card">
                            <BookListItemSkeleton />
                        </div>
                    ))}
                </div>
            ) : (
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    {Array.from({ length: 10 }).map((_, i) => (
                        <BookCardSkeleton key={i} />
                    ))}
                </div>
            )}
        </div>
    );
}

// ─── Results ──────────────────────────────────────────────────────────────────

function CatalogResults({
    books,
    hasActiveFilters,
    stats,
    filters,
    categories,
    viewMode,
}: {
    books: PaginatedBooks;
    hasActiveFilters: boolean;
    stats: CatalogProps['stats'];
    filters: CatalogProps['filters'];
    categories: CatalogProps['categories'];
    viewMode: 'grid' | 'list';
}) {
    return (
        <div className="flex flex-col gap-6">
            {/* Search result info */}
            {hasActiveFilters && (
                <p className="text-sm text-muted-foreground">
                    <span className="font-semibold text-foreground">
                        {stats.searchResultsCount.toLocaleString('id-ID')}
                    </span>{' '}
                    hasil ditemukan
                    {filters.category && (
                        <>
                            {' '}
                            dalam{' '}
                            <span className="font-semibold text-foreground">
                                {categories.find((c) => c.slug === filters.category)?.name}
                            </span>
                        </>
                    )}
                </p>
            )}

            {/* Book list / grid */}
            {books.data.length > 0 ? (
                viewMode === 'list' ? (
                    <div className="grid grid-cols-1 gap-3 lg:grid-cols-2">
                        {books.data.map((book) => (
                            <div
                                key={book.id}
                                className="overflow-hidden rounded-xl border bg-card"
                            >
                                <BookListItem book={book} />
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        {books.data.map((book) => (
                            <BookCard key={book.id} book={book} />
                        ))}
                    </div>
                )
            ) : (
                <Empty className="border-2 py-20">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <Library />
                        </EmptyMedia>
                        <EmptyTitle>Buku tidak ditemukan</EmptyTitle>
                        <EmptyDescription>
                            Coba kata kunci lain atau hapus filter yang aktif.
                        </EmptyDescription>
                    </EmptyHeader>
                </Empty>
            )}

            {/* Pagination */}
            {books.total > 0 && (
                <>
                    <Separator />
                    <Pagination books={books} />
                </>
            )}
        </div>
    );
}

// ─── Page ────────────────────────────────────────────────────────────────────

export default function Catalog({ filters, stats, categories, books }: CatalogProps) {
    const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');

    function clearAllFilters(): void {
        router.get(booksRoute.index.url(), {}, { preserveScroll: true, replace: true });
    }

    const hasActiveFilters = filters.search !== '' || filters.category !== '';

    return (
        <>
            <Head title="Katalog Buku — Ruang Baca" />

            {/* Subtle dot-grid texture */}
            <div
                className="pointer-events-none fixed inset-0 z-0 opacity-[0.03] dark:opacity-[0.05]"
                style={{
                    backgroundImage:
                        'radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0)',
                    backgroundSize: '24px 24px',
                }}
            />

            <div className="relative z-10 flex min-h-screen flex-col">
                <AppHeader hideSearch />

                <main className="flex-1">
                    <section className="py-10">
                        <div className="mx-auto max-w-7xl px-6 lg:px-8">
                            {/* Toolbar */}
                            <div className="mb-6 flex flex-wrap items-center gap-3">
                                <ToggleGroup
                                    type="single"
                                    value={viewMode}
                                    onValueChange={(val) =>
                                        val && setViewMode(val as 'grid' | 'list')
                                    }
                                    variant="outline"
                                >
                                    <ToggleGroupItem value="list" aria-label="Tampilan daftar">
                                        <LayoutList data-icon />
                                    </ToggleGroupItem>
                                    <ToggleGroupItem value="grid" aria-label="Tampilan grid">
                                        <LayoutGrid data-icon />
                                    </ToggleGroupItem>
                                </ToggleGroup>
                            </div>

                            {/* Active-filter chips */}
                            {hasActiveFilters && (
                                <div className="mb-4 flex flex-wrap items-center gap-2">
                                    {filters.search && (
                                        <Badge variant="secondary" className="gap-1.5 py-1">
                                            <span className="text-muted-foreground">
                                                Pencarian:
                                            </span>
                                            &ldquo;{filters.search}&rdquo;
                                        </Badge>
                                    )}
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        className="h-7 text-muted-foreground"
                                        onClick={clearAllFilters}
                                    >
                                        <X data-icon="inline-start" />
                                        Hapus filter
                                    </Button>
                                </div>
                            )}

                            {/* Results */}
                            <Deferred
                                data="books"
                                fallback={<ResultsSkeleton viewMode={viewMode} />}
                            >
                                <CatalogResults
                                    books={books}
                                    hasActiveFilters={hasActiveFilters}
                                    stats={stats}
                                    filters={filters}
                                    categories={categories}
                                    viewMode={viewMode}
                                />
                            </Deferred>
                        </div>
                    </section>
                </main>

                <Footer />
            </div>
        </>
    );
}
