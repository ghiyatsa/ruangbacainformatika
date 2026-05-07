import { Deferred, Head, router } from '@inertiajs/react';
import { LayoutGrid, LayoutList, Library, X } from 'lucide-react';
import { useState } from 'react';
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
import BookCard from '@/features/catalog/components/BookCard';
import { BookGridSkeleton } from '@/features/catalog/components/BookGridSkeleton';
import BookListItem from '@/features/catalog/components/BookListItem';
import { BookPagination } from '@/features/catalog/components/BookPagination';
import type {
    CategoryItem,
    CatalogStats,
    ViewMode,
} from '@/features/catalog/types';
import type { PaginatedBooks } from '@/features/welcome/types';
import booksRoute from '@/routes/books';

export interface CatalogPageProps {
    canRegister?: boolean;
    filters: { search: string; category: string };
    stats: CatalogStats;
    categories: CategoryItem[];
    books: PaginatedBooks;
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
    stats: CatalogStats;
    filters: CatalogPageProps['filters'];
    categories: CategoryItem[];
    viewMode: ViewMode;
}) {
    return (
        <div className="flex flex-col gap-6">
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
                                {
                                    categories.find(
                                        (c) => c.slug === filters.category,
                                    )?.name
                                }
                            </span>
                        </>
                    )}
                </p>
            )}

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

            {books.total > 0 && (
                <>
                    <Separator />
                    <BookPagination books={books} />
                </>
            )}
        </div>
    );
}

// ─── Page ─────────────────────────────────────────────────────────────────────

export default function CatalogPage({
    filters,
    stats,
    categories,
    books,
}: CatalogPageProps) {
    const [viewMode, setViewMode] = useState<ViewMode>('grid');

    const hasActiveFilters = filters.search !== '' || filters.category !== '';

    function clearAllFilters(): void {
        router.get(
            booksRoute.index.url(),
            {},
            { preserveScroll: true, replace: true },
        );
    }

    return (
        <>
            <Head title="Katalog Buku" />

            <div
                className="pointer-events-none fixed inset-0 z-0 opacity-[0.03] dark:opacity-[0.05]"
                style={{
                    backgroundImage:
                        'radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0)',
                    backgroundSize: '24px 24px',
                }}
            />

            <div className="relative z-10">
                <section className="py-10">
                    <div className="mx-auto max-w-7xl px-6 lg:px-8">
                        {/* Toolbar */}
                        <div className="mb-6 flex flex-wrap items-center gap-3">
                            <ToggleGroup
                                type="single"
                                value={viewMode}
                                onValueChange={(val) =>
                                    val && setViewMode(val as ViewMode)
                                }
                                variant="outline"
                            >
                                <ToggleGroupItem
                                    value="list"
                                    aria-label="Tampilan daftar"
                                >
                                    <LayoutList data-icon />
                                </ToggleGroupItem>
                                <ToggleGroupItem
                                    value="grid"
                                    aria-label="Tampilan grid"
                                >
                                    <LayoutGrid data-icon />
                                </ToggleGroupItem>
                            </ToggleGroup>
                        </div>

                        {/* Active-filter chips */}
                        {hasActiveFilters && (
                            <div className="mb-4 flex flex-wrap items-center gap-2">
                                {filters.search && (
                                    <Badge
                                        variant="secondary"
                                        className="gap-1.5 py-1"
                                    >
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
                            fallback={
                                <BookGridSkeleton viewMode={viewMode} />
                            }
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
            </div>
        </>
    );
}
