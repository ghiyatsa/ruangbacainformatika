import { Deferred, Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    BookOpen,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
    GraduationCap,
    Hash,
    LayoutGrid,
    LayoutList,
    Library,
} from 'lucide-react';
import { useState } from 'react';
import BookCard from '@/components/catalog/BookCard';
import BookCardSkeleton from '@/components/catalog/BookCardSkeleton';
import BookListItem from '@/components/catalog/BookListItem';
import BookListItemSkeleton from '@/components/catalog/BookListItemSkeleton';
import { AppHeader } from '@/components/layouts/AppHeader';
import { Badge } from '@/components/ui/badge';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { Button } from '@/components/ui/button';
import {
    Empty,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import Footer from '@/components/welcome/Footer';
import type { PaginatedBooks } from '@/components/welcome/types';
import booksRoute from '@/routes/books';
import categoriesRoute from '@/routes/books/categories';

// ─── Types ────────────────────────────────────────────────────────────────────

interface CategoryPageProps {
    category: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
    };
    filters: {
        search: string;
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
                                    pageLinks.find((l) => l.label === String(last))?.url ??
                                        null,
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

// ─── Page ─────────────────────────────────────────────────────────────────────

export default function CategoryPage({
    category,
    filters,
    stats,
    categories,
    books,
}: CategoryPageProps) {
    const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');

    const hasSearch = filters.search !== '';

    return (
        <>
            <Head title={`${category.name} — Katalog Buku · Ruang Baca`} />

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

                {/* ── Hero ── */}
                <CategoryHero category={category} stats={stats} />

                {/* ── Other categories strip ── */}
                {categories.length > 1 && (
                    <div className="border-b bg-muted/30">
                        <div className="mx-auto max-w-7xl px-6 lg:px-8">
                            <ScrollArea className="w-full">
                                <div className="flex gap-2 py-3">
                                    {categories
                                        .filter((c) => c.slug !== category.slug)
                                        .map((cat) => (
                                            <Link
                                                key={cat.id}
                                                href={categoriesRoute.show.url(cat.slug)}
                                                prefetch
                                                className="flex shrink-0 items-center gap-1.5 rounded-full border bg-background px-3 py-1.5 text-xs font-medium text-muted-foreground transition-all hover:border-primary/40 hover:bg-primary/5 hover:text-primary"
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
                    </div>
                )}

                {/* ── Main content ── */}
                <main className="flex-1 py-10">
                    <div className="mx-auto max-w-7xl px-6 lg:px-8">
                        {/* Toolbar */}
                        <div className="mb-6 flex flex-wrap items-center gap-3">
                            {/* View-mode toggle */}
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

                        {/* Active search chip */}
                        {hasSearch && (
                            <div className="mb-4 flex flex-wrap items-center gap-2">
                                <Badge variant="secondary" className="gap-1.5 py-1">
                                    <span className="text-muted-foreground">Pencarian:</span>
                                    &ldquo;{filters.search}&rdquo;
                                </Badge>
                            </div>
                        )}

                        {/* Results */}
                        <Deferred
                            data="books"
                            fallback={<CategorySkeleton viewMode={viewMode} />}
                        >
                            <CategoryResults
                                books={books}
                                hasSearch={hasSearch}
                                searchValue={filters.search}
                                stats={stats}
                                viewMode={viewMode}
                            />
                        </Deferred>
                    </div>
                </main>

                <Footer />
            </div>
        </>
    );
}

// ─── Hero ─────────────────────────────────────────────────────────────────────

function CategoryHero({
    category,
    stats,
}: {
    category: CategoryPageProps['category'];
    stats: CategoryPageProps['stats'];
}) {
    return (
        <div className="relative overflow-hidden border-b bg-gradient-to-br from-background via-muted/30 to-background">
            <div className="absolute inset-0 bg-gradient-to-r from-primary/5 via-transparent to-primary/5" />

            <div className="relative mx-auto max-w-7xl px-6 pt-8 pb-10 lg:px-8">
                {/* Breadcrumb */}
                <Breadcrumb className="mb-6">
                    <BreadcrumbList>
                        <BreadcrumbItem>
                            <BreadcrumbLink asChild>
                                <Link href="/">Beranda</Link>
                            </BreadcrumbLink>
                        </BreadcrumbItem>
                        <BreadcrumbSeparator />
                        <BreadcrumbItem>
                            <BreadcrumbLink asChild>
                                <Link href={booksRoute.index.url()}>Katalog</Link>
                            </BreadcrumbLink>
                        </BreadcrumbItem>
                        <BreadcrumbSeparator />
                        <BreadcrumbItem>
                            <BreadcrumbPage>{category.name}</BreadcrumbPage>
                        </BreadcrumbItem>
                    </BreadcrumbList>
                </Breadcrumb>

                <div className="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                    <div className="flex flex-col gap-4">
                        {/* Icon + badge */}
                        <div className="flex items-center gap-2.5">
                            <div className="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <GraduationCap className="size-4" />
                            </div>
                            <Badge variant="secondary" className="gap-1.5">
                                <Hash data-icon="inline-start" />
                                Kategori
                            </Badge>
                        </div>

                        {/* Title */}
                        <div>
                            <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                                {category.name}
                            </h1>
                            {category.description && (
                                <p className="mt-2 max-w-xl text-base text-muted-foreground">
                                    {category.description}
                                </p>
                            )}
                        </div>

                        {/* Stats */}
                        <div className="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                            <span className="flex items-center gap-1.5">
                                <BookOpen className="size-4" />
                                <span className="font-semibold text-foreground">
                                    {stats.searchResultsCount.toLocaleString('id-ID')}
                                </span>{' '}
                                judul buku
                            </span>
                            <span className="flex items-center gap-1.5">
                                <Library className="size-4" />
                                <span className="font-semibold text-foreground">
                                    {stats.availableItemsCount.toLocaleString('id-ID')}
                                </span>{' '}
                                eksemplar tersedia
                            </span>
                        </div>
                    </div>

                    <Button variant="ghost" size="sm" asChild>
                        <Link href={booksRoute.index.url()}>
                            <ArrowLeft data-icon="inline-start" />
                            Kembali ke Katalog
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    );
}

// ─── Skeleton ─────────────────────────────────────────────────────────────────

function CategorySkeleton({ viewMode }: { viewMode: 'grid' | 'list' }) {
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

function CategoryResults({
    books,
    hasSearch,
    searchValue,
    stats,
    viewMode,
}: {
    books: PaginatedBooks;
    hasSearch: boolean;
    searchValue: string;
    stats: CategoryPageProps['stats'];
    viewMode: 'grid' | 'list';
}) {
    return (
        <div className="flex flex-col gap-6">
            {/* Result count — only when searching */}
            {hasSearch && (
                <p className="text-sm text-muted-foreground">
                    <span className="font-semibold text-foreground">
                        {stats.searchResultsCount.toLocaleString('id-ID')}
                    </span>{' '}
                    hasil untuk &ldquo;{searchValue}&rdquo;
                </p>
            )}

            {/* Books */}
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
                            {hasSearch
                                ? 'Coba kata kunci lain untuk menemukan buku.'
                                : 'Belum ada buku yang dipublikasikan di kategori ini.'}
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
