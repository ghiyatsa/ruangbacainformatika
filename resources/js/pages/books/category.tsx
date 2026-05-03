import { Deferred, Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    BookOpen,
    ChevronLeft,
    ChevronRight,
    GraduationCap,
    Hash,
    LayoutGrid,
    LayoutList,
    Library,
    Search,
    X,
} from 'lucide-react';
import { useCallback, useRef, useState } from 'react';
import type { ChangeEvent } from 'react';
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
import { Input } from '@/components/ui/input';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import Footer from '@/components/welcome/Footer';
import type { PaginatedBooks } from '@/components/welcome/types';
import { cn } from '@/lib/utils';
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

// ─── Page ─────────────────────────────────────────────────────────────────────

export default function CategoryPage({
    category,
    filters,
    stats,
    categories,
    books,
}: CategoryPageProps) {
    const [searchValue, setSearchValue] = useState(filters.search);
    const [viewMode, setViewMode] = useState<'grid' | 'list'>('list');
    const searchTimeout = useRef<ReturnType<typeof setTimeout> | null>(null);

    const applyFilters = useCallback(
        (params: { search?: string }) => {
            router.get(
                categoriesRoute.show.url(category.slug),
                { search: params.search ?? filters.search },
                { preserveScroll: true, replace: true },
            );
        },
        [category.slug, filters.search],
    );

    function handleSearchChange(value: string): void {
        setSearchValue(value);
        if (searchTimeout.current) {
            clearTimeout(searchTimeout.current);
        }
        searchTimeout.current = setTimeout(() => {
            applyFilters({ search: value });
        }, 400);
    }

    function clearSearch(): void {
        setSearchValue('');
        router.get(
            categoriesRoute.show.url(category.slug),
            {},
            { preserveScroll: true, replace: true },
        );
    }

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
                <CategoryHero
                    category={category}
                    stats={stats}
                />

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
                        {/* Toolbar — mirrors catalog.tsx */}
                        <div className="mb-6 flex flex-wrap items-center gap-3">
                            {/* Search */}
                            <div className="relative flex-1">
                                <Search className="pointer-events-none absolute top-1/2 left-3 size-3.5 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    id="category-search"
                                    value={searchValue}
                                    onChange={(e: ChangeEvent<HTMLInputElement>) =>
                                        handleSearchChange(e.target.value)
                                    }
                                    placeholder={`Cari di ${category.name}…`}
                                    className="h-9 pr-8 pl-9 text-sm"
                                />
                                {searchValue && (
                                    <button
                                        type="button"
                                        onClick={clearSearch}
                                        className="absolute top-1/2 right-2.5 -translate-y-1/2 text-muted-foreground transition-colors hover:text-foreground"
                                        aria-label="Hapus pencarian"
                                    >
                                        <X className="size-3.5" />
                                    </button>
                                )}
                            </div>

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
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="h-7 text-muted-foreground"
                                    onClick={clearSearch}
                                >
                                    <X data-icon="inline-start" />
                                    Hapus filter
                                </Button>
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
                                    {stats.searchResultsCount}
                                </span>{' '}
                                judul buku
                            </span>
                            <span className="flex items-center gap-1.5">
                                <Library className="size-4" />
                                <span className="font-semibold text-foreground">
                                    {stats.availableItemsCount}
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
        <div className="flex flex-col gap-4">
            {/* Result count — only when searching */}
            {hasSearch && (
                <p className="text-sm text-muted-foreground">
                    <span className="font-semibold text-foreground">
                        {stats.searchResultsCount}
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
                                    <Link href={books.prev_page_url} preserveScroll>
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
                                    <Link href={books.next_page_url} preserveScroll>
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
