import { Deferred, Head, Link, router } from '@inertiajs/react';
import {
    ArrowRight,
    BookMarked,
    ChevronLeft,
    ChevronRight,
    GraduationCap,
    Library,
    Search,
    Sparkles,
    X,
} from 'lucide-react';
import { LayoutGrid, LayoutList } from 'lucide-react';
import { useCallback, useRef, useState } from 'react';
import type { ChangeEvent } from 'react';
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
import { Input } from '@/components/ui/input';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import Footer from '@/components/welcome/Footer';
import type { PaginatedBooks } from '@/components/welcome/types';
import { cn } from '@/lib/utils';
import booksRoute from '@/routes/books';
import categoriesRoute from '@/routes/books/categories';

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

// ─── Constants ───────────────────────────────────────────────────────────────

const CATEGORY_GRADIENTS = [
    'from-violet-500/15 to-purple-500/5 border-violet-500/20 hover:border-violet-500/40',
    'from-blue-500/15 to-cyan-500/5 border-blue-500/20 hover:border-blue-500/40',
    'from-emerald-500/15 to-teal-500/5 border-emerald-500/20 hover:border-emerald-500/40',
    'from-amber-500/15 to-orange-500/5 border-amber-500/20 hover:border-amber-500/40',
    'from-rose-500/15 to-pink-500/5 border-rose-500/20 hover:border-rose-500/40',
    'from-indigo-500/15 to-blue-500/5 border-indigo-500/20 hover:border-indigo-500/40',
    'from-teal-500/15 to-emerald-500/5 border-teal-500/20 hover:border-teal-500/40',
    'from-fuchsia-500/15 to-pink-500/5 border-fuchsia-500/20 hover:border-fuchsia-500/40',
];

// ─── Page ────────────────────────────────────────────────────────────────────

export default function Catalog({
    filters,
    stats,
    categories,
    books,
}: CatalogProps) {
    const [searchValue, setSearchValue] = useState(filters.search);
    const [isSearching, setIsSearching] = useState(false);
    const [viewMode, setViewMode] = useState<'grid' | 'list'>('list');
    const searchTimeout = useRef<ReturnType<typeof setTimeout> | null>(null);

    const applyFilters = useCallback(
        (params: { search?: string; category?: string }) => {
            router.get(
                booksRoute.index.url(),
                {
                    search: params.search ?? filters.search,
                    category: params.category ?? filters.category,
                },
                { preserveScroll: true, replace: true },
            );
        },
        [filters.category, filters.search],
    );

    function handleSearchChange(value: string): void {
        setSearchValue(value);
        setIsSearching(true);

        if (searchTimeout.current) {
            clearTimeout(searchTimeout.current);
        }

        searchTimeout.current = setTimeout(() => {
            applyFilters({ search: value });
            // The loading state will be handled by the Inertia router finishing,
            // but for simple UI feedback we can just keep it until the next render
            // Or better: use Inertia events to reset it.
            // But let's keep it simple for now.
            setIsSearching(false);
        }, 500);
    }

    function clearAllFilters(): void {
        setSearchValue('');
        router.get(
            booksRoute.index.url(),
            {},
            { preserveScroll: true, replace: true },
        );
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
                <AppHeader />

                <main className="flex-1">
                    {/* ── Hero ── */}
                    <HeroSection
                        stats={stats}
                        categories={categories}
                        hasActiveFilters={hasActiveFilters}
                    />

                    {/* ── Catalog body ── */}
                    <section className="py-10">
                        <div className="mx-auto max-w-7xl px-6 lg:px-8">
                            {/* Toolbar */}
                            <div className="mb-6 flex flex-wrap items-center gap-3">
                                {/* Search */}
                                <div className="relative flex-1">
                                    <Search className="pointer-events-none absolute top-1/2 left-3 size-3.5 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        id="catalog-search"
                                        value={searchValue}
                                        onChange={(
                                            e: ChangeEvent<HTMLInputElement>,
                                        ) => handleSearchChange(e.target.value)}
                                        placeholder="Cari judul, penulis, penerbit…"
                                        className="h-10 pr-10 pl-10 text-sm transition-all focus:ring-primary/20"
                                    />
                                    <div className="absolute top-1/2 right-3 -translate-y-1/2 flex items-center gap-2">
                                        {isSearching ? (
                                            <Spinner className="size-3.5 text-primary" />
                                        ) : searchValue ? (
                                            <button
                                                type="button"
                                                onClick={clearAllFilters}
                                                className="text-muted-foreground transition-colors hover:text-foreground"
                                                aria-label="Hapus pencarian"
                                            >
                                                <X className="size-3.5" />
                                            </button>
                                        ) : null}
                                    </div>
                                </div>

                                {/* View-mode toggle */}
                                <ToggleGroup
                                    type="single"
                                    value={viewMode}
                                    onValueChange={(val) =>
                                        val &&
                                        setViewMode(val as 'grid' | 'list')
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
                                    <ResultsSkeleton viewMode={viewMode} />
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
                </main>

                <Footer />
            </div>
        </>
    );
}

// ─── Hero ─────────────────────────────────────────────────────────────────────

function HeroSection({
    stats,
    categories,
    hasActiveFilters,
}: {
    stats: CatalogProps['stats'];
    categories: CatalogProps['categories'];
    hasActiveFilters: boolean;
}) {
    return (
        <div className="relative overflow-hidden border-b bg-gradient-to-br from-background via-muted/30 to-background">
            <div className="absolute inset-0 bg-gradient-to-r from-primary/5 via-transparent to-primary/5" />

            <div className="relative mx-auto max-w-7xl px-6 pt-12 pb-10 lg:px-8">
                {/* Icon + badge */}
                <div className="mb-4 flex items-center gap-2.5">
                    <div className="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <BookMarked className="size-4" />
                    </div>
                    <Badge variant="secondary" className="gap-1.5">
                        <Sparkles data-icon="inline-start" />
                        Koleksi Akademik
                    </Badge>
                </div>

                {/* Title */}
                <h1 className="mb-2 text-4xl font-bold tracking-tight sm:text-5xl">
                    Katalog Buku
                </h1>
                <p className="mb-8 max-w-xl text-base text-muted-foreground">
                    Jelajahi{' '}
                    <span className="font-semibold text-foreground">
                        {stats.booksCount}
                    </span>{' '}
                    judul buku dengan{' '}
                    <span className="font-semibold text-foreground">
                        {stats.availableItemsCount}
                    </span>{' '}
                    eksemplar siap dipinjam.
                </p>

                {/* Category quick-nav — only when no filter is active */}
                {categories.length > 0 && !hasActiveFilters && (
                    <div className="flex flex-col gap-3">
                        <div className="flex items-center justify-between">
                            <span className="flex items-center gap-2 text-sm font-semibold text-foreground">
                                <GraduationCap className="size-4 text-muted-foreground" />
                                Telusuri per Kategori
                            </span>
                            <Link
                                href={booksRoute.index.url()}
                                className="flex items-center gap-1 text-xs text-muted-foreground transition-colors hover:text-foreground"
                            >
                                Lihat semua
                                <ArrowRight className="size-3" />
                            </Link>
                        </div>

                        <ScrollArea className="w-full">
                            <div className="flex flex-wrap gap-2 pb-1">
                                {categories.slice(0, 14).map((cat, i) => (
                                    <Link
                                        key={cat.id}
                                        href={categoriesRoute.show.url(
                                            cat.slug,
                                        )}
                                        prefetch
                                        className={cn(
                                            'flex items-center gap-2 rounded-xl border bg-gradient-to-br px-4 py-2 text-sm font-medium transition-all duration-200 hover:scale-[1.02] hover:shadow-sm',
                                            CATEGORY_GRADIENTS[
                                                i % CATEGORY_GRADIENTS.length
                                            ],
                                        )}
                                    >
                                        {cat.name}
                                        <Badge
                                            variant="secondary"
                                            className="px-1.5 py-0 text-[10px]"
                                        >
                                            {cat.booksCount}
                                        </Badge>
                                    </Link>
                                ))}
                            </div>
                            <ScrollBar orientation="horizontal" />
                        </ScrollArea>
                    </div>
                )}
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
                    {Array.from({ length: 6 }).map((_, i) => (
                        <div
                            key={i}
                            className="overflow-hidden rounded-xl border bg-card"
                        >
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
        <div className="flex flex-col gap-4">
            {/* Search result info — only shown when a filter is active */}
            {hasActiveFilters && (
                <p className="text-sm text-muted-foreground">
                    <span className="font-semibold text-foreground">
                        {stats.searchResultsCount}
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
                    <div className="flex flex-col items-center justify-between gap-3 sm:flex-row">
                        <p className="text-sm text-muted-foreground">
                            <span className="font-semibold text-foreground">
                                {books.from}–{books.to}
                            </span>{' '}
                            dari{' '}
                            <span className="font-semibold text-foreground">
                                {books.total}
                            </span>{' '}
                            buku
                        </p>

                        <div className="flex items-center gap-2">
                            <Button
                                asChild={!!books.prev_page_url}
                                disabled={!books.prev_page_url}
                                variant="outline"
                                size="sm"
                            >
                                {books.prev_page_url ? (
                                    <Link
                                        href={books.prev_page_url}
                                        preserveScroll
                                    >
                                        <ChevronLeft data-icon="inline-start" />
                                        Sebelumnya
                                    </Link>
                                ) : (
                                    <span>
                                        <ChevronLeft data-icon="inline-start" />
                                        Sebelumnya
                                    </span>
                                )}
                            </Button>

                            <span className="px-1 text-xs text-muted-foreground">
                                {books.current_page} / {books.last_page}
                            </span>

                            <Button
                                asChild={!!books.next_page_url}
                                disabled={!books.next_page_url}
                                variant="outline"
                                size="sm"
                            >
                                {books.next_page_url ? (
                                    <Link
                                        href={books.next_page_url}
                                        preserveScroll
                                    >
                                        Berikutnya
                                        <ChevronRight data-icon="inline-end" />
                                    </Link>
                                ) : (
                                    <span>
                                        Berikutnya
                                        <ChevronRight data-icon="inline-end" />
                                    </span>
                                )}
                            </Button>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}
