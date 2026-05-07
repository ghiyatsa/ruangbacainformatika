import { Deferred, Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    BookOpen,
    GraduationCap,
    Hash,
    LayoutGrid,
    LayoutList,
    Library,
} from 'lucide-react';
import { useState } from 'react';
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
import BookCard from '@/features/catalog/components/BookCard';
import { BookGridSkeleton } from '@/features/catalog/components/BookGridSkeleton';
import BookListItem from '@/features/catalog/components/BookListItem';
import { BookPagination } from '@/features/catalog/components/BookPagination';
import type {
    CatalogStats,
    CategoryItem,
    ViewMode,
} from '@/features/catalog/types';
import type { PaginatedBooks } from '@/features/welcome/types';
import booksRoute from '@/routes/books';
import categoriesRoute from '@/routes/books/categories';

export interface CategoryPageProps {
    category: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
    };
    filters: { search: string };
    stats: CatalogStats;
    categories: CategoryItem[];
    books: PaginatedBooks;
}

// ─── Hero ─────────────────────────────────────────────────────────────────────

function CategoryHero({
    category,
    stats,
}: {
    category: CategoryPageProps['category'];
    stats: CatalogStats;
}) {
    return (
        <div className="relative -mt-20 overflow-hidden border-b bg-linear-to-br from-background via-muted/30 to-background sm:-mt-28">
            <div className="absolute inset-0 bg-linear-to-r from-primary/5 via-transparent to-primary/5" />

            <div className="relative mx-auto max-w-7xl px-6 pt-28 pb-10 sm:pt-36 lg:px-8">
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
                                <Link href={booksRoute.index.url()}>
                                    Katalog
                                </Link>
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
                        <div className="flex items-center gap-2.5">
                            <div className="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <GraduationCap className="size-4" />
                            </div>
                            <Badge variant="secondary" className="gap-1.5">
                                <Hash data-icon="inline-start" />
                                Kategori
                            </Badge>
                        </div>

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

                        <div className="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                            <span className="flex items-center gap-1.5">
                                <BookOpen className="size-4" />
                                <span className="font-semibold text-foreground">
                                    {stats.searchResultsCount.toLocaleString(
                                        'id-ID',
                                    )}
                                </span>{' '}
                                judul buku
                            </span>
                            <span className="flex items-center gap-1.5">
                                <Library className="size-4" />
                                <span className="font-semibold text-foreground">
                                    {stats.availableItemsCount.toLocaleString(
                                        'id-ID',
                                    )}
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
    stats: CatalogStats;
    viewMode: ViewMode;
}) {
    return (
        <div className="flex flex-col gap-6">
            {hasSearch && (
                <p className="text-sm text-muted-foreground">
                    <span className="font-semibold text-foreground">
                        {stats.searchResultsCount.toLocaleString('id-ID')}
                    </span>{' '}
                    hasil untuk &ldquo;{searchValue}&rdquo;
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
                            {hasSearch
                                ? 'Coba kata kunci lain untuk menemukan buku.'
                                : 'Belum ada buku yang dipublikasikan di kategori ini.'}
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

export default function CategoryPage({
    category,
    filters,
    stats,
    categories,
    books,
}: CategoryPageProps) {
    const [viewMode, setViewMode] = useState<ViewMode>('grid');
    const hasSearch = filters.search !== '';

    return (
        <>
            <Head title={`${category.name} — Katalog Buku`} />

            <div
                className="pointer-events-none fixed inset-0 z-0 opacity-[0.03] dark:opacity-[0.05]"
                style={{
                    backgroundImage:
                        'radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0)',
                    backgroundSize: '24px 24px',
                }}
            />

            <div className="relative z-10">
                <CategoryHero category={category} stats={stats} />

                {/* Related categories strip */}
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
                                                href={categoriesRoute.show.url(
                                                    cat.slug,
                                                )}
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

                <div className="py-10">
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

                        {hasSearch && (
                            <div className="mb-4 flex flex-wrap items-center gap-2">
                                <Badge
                                    variant="secondary"
                                    className="gap-1.5 py-1"
                                >
                                    <span className="text-muted-foreground">
                                        Pencarian:
                                    </span>
                                    &ldquo;{filters.search}&rdquo;
                                </Badge>
                            </div>
                        )}

                        <Deferred
                            data="books"
                            fallback={<BookGridSkeleton viewMode={viewMode} />}
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
                </div>
            </div>
        </>
    );
}
