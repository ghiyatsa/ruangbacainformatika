import { Head, Link, router } from '@inertiajs/react';
import {
    BookOpen,
    ChevronLeft,
    ChevronRight,
    GraduationCap,
    LayoutGrid,
    LayoutList,
    Library,
    SlidersHorizontal,
} from 'lucide-react';
import { useCallback, useRef, useState } from 'react';
import type { ChangeEvent } from 'react';
import BookController from '@/actions/App/Http/Controllers/BookController';
import CatalogController from '@/actions/App/Http/Controllers/CatalogController';
import BookCard from '@/components/catalog/BookCard';
import FilterSidebar from '@/components/catalog/FilterSidebar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import Footer from '@/components/welcome/Footer';
import Navigation from '@/components/welcome/Navigation';
import type { CatalogBook, PaginatedBooks } from '@/components/welcome/types';

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

export default function Catalog({
    canRegister = true,
    filters,
    stats,
    categories,
    books,
}: CatalogProps) {
    const [mobileFiltersOpen, setMobileFiltersOpen] = useState(false);
    const [searchValue, setSearchValue] = useState(filters.search);
    const [viewMode, setViewMode] = useState<'grid' | 'list'>('list');
    const searchTimeout = useRef<ReturnType<typeof setTimeout> | null>(null);

    const applyFilters = useCallback(
        (params: { search?: string; category?: string }) => {
            router.get(
                CatalogController.url(),
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

        if (searchTimeout.current) {
            clearTimeout(searchTimeout.current);
        }

        searchTimeout.current = setTimeout(() => {
            applyFilters({ search: value, category: filters.category });
        }, 400);
    }

    function handleCategoryChange(slug: string): void {
        const nextCategory = slug === filters.category ? '' : slug;
        applyFilters({ search: filters.search, category: nextCategory });
    }

    function clearAllFilters(): void {
        setSearchValue('');
        router.get(
            CatalogController.url(),
            {},
            { preserveScroll: true, replace: true },
        );
    }

    const hasActiveFilters = filters.search !== '' || filters.category !== '';

    return (
        <div className="min-h-screen bg-background font-sans text-foreground selection:bg-primary/10 selection:text-primary">
            <Head title="Katalog Buku — Ruang Baca" />

            <div
                className="pointer-events-none fixed inset-0 z-0 opacity-[0.03] dark:opacity-[0.05]"
                style={{
                    backgroundImage:
                        'radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0)',
                    backgroundSize: '24px 24px',
                }}
            />

            <div className="relative z-10 flex min-h-screen flex-col">
                <Navigation canRegister={canRegister} />

                <main className="flex-1 py-10">
                    <div className="mx-auto max-w-7xl px-6 lg:px-8">
                        <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <Badge
                                    variant="secondary"
                                    className="mb-3 w-fit"
                                >
                                    <GraduationCap className="mr-1.5 size-3.5" />
                                    Koleksi Akademik
                                </Badge>
                                <h1 className="text-3xl font-bold tracking-tight sm:text-4xl">
                                    Katalog Buku
                                </h1>
                                <p className="mt-1.5 text-sm text-muted-foreground">
                                    {stats.booksCount} judul &middot;{' '}
                                    {stats.availableItemsCount} eksemplar
                                    tersedia
                                </p>
                            </div>

                            <div className="flex items-center gap-1 self-start rounded-lg border bg-muted/50 p-1">
                                <Button
                                    variant={
                                        viewMode === 'grid'
                                            ? 'secondary'
                                            : 'ghost'
                                    }
                                    size="icon"
                                    className="size-8"
                                    onClick={() => setViewMode('grid')}
                                >
                                    <LayoutGrid className="size-4" />
                                    <span className="sr-only">Grid view</span>
                                </Button>
                                <Button
                                    variant={
                                        viewMode === 'list'
                                            ? 'secondary'
                                            : 'ghost'
                                    }
                                    size="icon"
                                    className="size-8"
                                    onClick={() => setViewMode('list')}
                                >
                                    <LayoutList className="size-4" />
                                    <span className="sr-only">List view</span>
                                </Button>
                            </div>
                        </div>

                        <div className="flex gap-8 lg:items-start">
                            <FilterSidebar
                                searchValue={searchValue}
                                onSearchChange={handleSearchChange}
                                categories={categories}
                                activeCategory={filters.category}
                                onCategoryChange={handleCategoryChange}
                                onClearFilters={clearAllFilters}
                                hasActiveFilters={hasActiveFilters}
                                className="hidden w-56 shrink-0 lg:block"
                            />

                            <div className="min-w-0 flex-1">
                                <div className="mb-4 flex gap-2 lg:hidden">
                                    <div className="relative flex-1">
                                        <Input
                                            value={searchValue}
                                            onChange={(
                                                event: ChangeEvent<HTMLInputElement>,
                                            ) =>
                                                handleSearchChange(
                                                    event.target.value,
                                                )
                                            }
                                            placeholder="Cari buku..."
                                            className="h-9 text-sm"
                                        />
                                    </div>

                                    <Sheet
                                        open={mobileFiltersOpen}
                                        onOpenChange={setMobileFiltersOpen}
                                    >
                                        <SheetTrigger asChild>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                className="shrink-0 gap-1.5"
                                            >
                                                <SlidersHorizontal className="size-3.5" />
                                                Filter
                                            </Button>
                                        </SheetTrigger>

                                        <SheetContent
                                            side="right"
                                            className="w-full sm:max-w-sm"
                                        >
                                            <SheetHeader>
                                                <SheetTitle>
                                                    Filter katalog
                                                </SheetTitle>
                                                <SheetDescription>
                                                    Persempit hasil pencarian
                                                    berdasarkan kata kunci dan
                                                    kategori.
                                                </SheetDescription>
                                            </SheetHeader>

                                            <div className="px-4 pb-4">
                                                <FilterSidebar
                                                    searchValue={searchValue}
                                                    onSearchChange={
                                                        handleSearchChange
                                                    }
                                                    categories={categories}
                                                    activeCategory={
                                                        filters.category
                                                    }
                                                    onCategoryChange={
                                                        handleCategoryChange
                                                    }
                                                    onClearFilters={
                                                        clearAllFilters
                                                    }
                                                    hasActiveFilters={
                                                        hasActiveFilters
                                                    }
                                                    className="w-full"
                                                    onFilterApplied={() =>
                                                        setMobileFiltersOpen(
                                                            false,
                                                        )
                                                    }
                                                />
                                            </div>
                                        </SheetContent>
                                    </Sheet>
                                </div>

                                <div className="mb-4 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-muted-foreground">
                                    {hasActiveFilters ? (
                                        <>
                                            <span className="font-semibold text-foreground">
                                                {stats.searchResultsCount}
                                            </span>
                                            <span>hasil ditemukan</span>
                                            {filters.search && (
                                                <span className="rounded bg-muted px-1.5 py-0.5 text-xs font-medium text-foreground">
                                                    "{filters.search}"
                                                </span>
                                            )}
                                            {filters.category && (
                                                <span className="rounded bg-primary/10 px-1.5 py-0.5 text-xs font-medium text-primary">
                                                    {
                                                        categories.find(
                                                            (category) =>
                                                                category.slug ===
                                                                filters.category,
                                                        )?.name
                                                    }
                                                </span>
                                            )}
                                        </>
                                    ) : (
                                        <span>
                                            Menampilkan{' '}
                                            <span className="font-semibold text-foreground">
                                                {books.from}–{books.to}
                                            </span>{' '}
                                            dari{' '}
                                            <span className="font-semibold text-foreground">
                                                {books.total}
                                            </span>{' '}
                                            buku
                                        </span>
                                    )}
                                </div>

                                {books.data.length > 0 ? (
                                    <>
                                        {viewMode === 'list' ? (
                                            <div className="overflow-hidden rounded-xl border bg-card">
                                                <div className="flex flex-col divide-y divide-border">
                                                    {books.data.map((book) => (
                                                        <BookListItem
                                                            key={book.id}
                                                            book={book}
                                                        />
                                                    ))}
                                                </div>
                                            </div>
                                        ) : (
                                            <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                                                {books.data.map((book) => (
                                                    <BookCard
                                                        key={book.id}
                                                        book={book}
                                                    />
                                                ))}
                                            </div>
                                        )}
                                    </>
                                ) : (
                                    <div className="flex flex-col items-center justify-center rounded-xl border-2 border-dashed py-20 text-center">
                                        <div className="mb-3 rounded-full bg-muted p-4">
                                            <Library className="size-8 text-muted-foreground" />
                                        </div>
                                        <h2 className="text-base font-bold">
                                            Buku tidak ditemukan
                                        </h2>
                                        <p className="mt-1 max-w-xs text-sm text-muted-foreground">
                                            Coba kata kunci lain atau hapus
                                            filter aktif.
                                        </p>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            className="mt-5"
                                            onClick={clearAllFilters}
                                        >
                                            Reset Filter
                                        </Button>
                                    </div>
                                )}

                                {books.total > 0 && (
                                    <div className="mt-6 flex flex-col items-center justify-between gap-3 border-t pt-6 sm:flex-row">
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
                                                className="gap-1"
                                            >
                                                {books.prev_page_url ? (
                                                    <Link
                                                        href={
                                                            books.prev_page_url
                                                        }
                                                        preserveScroll
                                                    >
                                                        <ChevronLeft className="size-3.5" />
                                                        Sebelumnya
                                                    </Link>
                                                ) : (
                                                    <span className="flex items-center gap-1">
                                                        <ChevronLeft className="size-3.5" />
                                                        Sebelumnya
                                                    </span>
                                                )}
                                            </Button>
                                            <span className="px-1 text-xs text-muted-foreground">
                                                {books.current_page} /{' '}
                                                {books.last_page}
                                            </span>
                                            <Button
                                                asChild={!!books.next_page_url}
                                                disabled={!books.next_page_url}
                                                variant="outline"
                                                size="sm"
                                                className="gap-1"
                                            >
                                                {books.next_page_url ? (
                                                    <Link
                                                        href={
                                                            books.next_page_url
                                                        }
                                                        preserveScroll
                                                    >
                                                        Berikutnya
                                                        <ChevronRight className="size-3.5" />
                                                    </Link>
                                                ) : (
                                                    <span className="flex items-center gap-1">
                                                        Berikutnya
                                                        <ChevronRight className="size-3.5" />
                                                    </span>
                                                )}
                                            </Button>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </main>

                <Footer />
            </div>
        </div>
    );
}

function BookListItem({ book }: { book: CatalogBook }) {
    return (
        <Link
            href={BookController.show(book.slug)}
            className="group flex items-center gap-4 px-4 py-3 transition-colors hover:bg-muted/40 focus:bg-muted/40 focus:outline-none"
        >
            <div className="h-16 w-11 shrink-0 overflow-hidden rounded-md border bg-muted shadow-sm">
                <img
                    src={book.coverImageUrl}
                    alt={book.title}
                    className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
                />
            </div>

            <div className="flex min-w-0 flex-1 flex-col gap-0.5">
                <div className="flex flex-wrap items-center gap-1.5">
                    {book.categories.slice(0, 1).map((category) => (
                        <span
                            key={category}
                            className="rounded border px-1.5 py-px text-[10px] font-medium text-muted-foreground"
                        >
                            {category}
                        </span>
                    ))}
                    {book.isFeatured && (
                        <span className="rounded bg-primary/10 px-1.5 py-px text-[10px] font-semibold text-primary">
                            ✦ Unggulan
                        </span>
                    )}
                </div>
                <p className="line-clamp-1 text-sm leading-snug font-semibold transition-colors group-hover:text-primary">
                    {book.title}
                </p>
                <p className="line-clamp-1 text-xs text-muted-foreground">
                    {book.authors.join(', ') || 'Penulis anonim'}
                    {book.publishedYear && (
                        <span className="ml-2 text-[11px]">
                            · {book.publishedYear}
                        </span>
                    )}
                </p>
            </div>

            <div className="shrink-0">
                <Badge
                    variant={
                        !book.isAvailable
                            ? 'outline'
                            : book.isBorrowable
                              ? 'default'
                              : 'secondary'
                    }
                    className="text-[10px]"
                >
                    {!book.isAvailable
                        ? 'Kosong'
                        : !book.isBorrowable
                          ? 'Referensi'
                          : `${book.availableItemsCount} tersedia`}
                </Badge>
            </div>

            <div className="hidden shrink-0 items-center gap-1 text-[11px] text-muted-foreground sm:flex">
                <BookOpen className="size-3" />
                <span>{book.pages ? `${book.pages} hal` : '—'}</span>
            </div>
        </Link>
    );
}
