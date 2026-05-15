import { router } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { CatalogPage } from '@/components/catalog/CatalogPage';
import { Button } from '@/components/ui/button';
import { BookCatalogFilters } from '@/features/books/components/BookCatalogFilters';
import { BookCatalogResults } from '@/features/books/components/BookCatalogResults';
import type { BookCatalogPageProps, ViewMode } from '@/features/books/types';
import booksRoute from '@/routes/books';

export default function BookCatalogPage({
    filters,
    stats,
    categories,
    years,
    books,
}: BookCatalogPageProps) {
    const [viewMode, setViewMode] = useState<ViewMode>('grid');
    const [isLoadingMore, setIsLoadingMore] = useState(false);
    const [isAutoLoadEnabled, setIsAutoLoadEnabled] = useState(false);
    const autoLoadTriggerRef = useRef<HTMLDivElement | null>(null);

    function clearAllFilters(): void {
        setIsAutoLoadEnabled(false);

        router.get(
            booksRoute.index.url(),
            {},
            { preserveScroll: true, replace: true },
        );
    }

    function removeFilter(key: string): void {
        const next = { ...filters };

        if (key === 'search') {
            next.search = '';
        } else if (key === 'year') {
            next.year = null;
        } else if (key === 'featured') {
            next.featured = false;
        } else if (key === 'availability') {
            next.availability = false;
        }

        setIsAutoLoadEnabled(false);

        router.get(booksRoute.index.url(), next, {
            preserveScroll: true,
            replace: true,
        });
    }

    function loadMoreBooks(): void {
        if (!books?.next_page_url || isLoadingMore) {
            return;
        }

        router.visit(books.next_page_url, {
            only: ['books'],
            preserveScroll: true,
            preserveState: true,
            onStart: () => setIsLoadingMore(true),
            onFinish: () => setIsLoadingMore(false),
        });
    }

    function enableAutoLoad(): void {
        if (isAutoLoadEnabled) {
            return;
        }

        setIsAutoLoadEnabled(true);
        loadMoreBooks();
    }

    useEffect(() => {
        if (!isAutoLoadEnabled || !books?.next_page_url || isLoadingMore) {
            return;
        }

        const triggerElement = autoLoadTriggerRef.current;

        if (!triggerElement) {
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                const [entry] = entries;

                if (!entry?.isIntersecting || isLoadingMore) {
                    return;
                }

                loadMoreBooks();
            },
            {
                rootMargin: '240px 0px',
                threshold: 0.1,
            },
        );

        observer.observe(triggerElement);

        return () => observer.disconnect();
    }, [books?.next_page_url, isAutoLoadEnabled, isLoadingMore]);

    return (
        <CatalogPage
            title="Katalog Buku"
            metaDescription="Jelajahi katalog buku Ruang Baca Teknik Informatika Universitas Malikussaleh untuk referensi kuliah, riset, dan pembelajaran."
            resourceName="judul buku"
            breadcrumbLabel="Katalog Buku"
            totalCount={stats.booksCount ?? 0}
            paginationData={books}
            filters={filters}
            onClearFilters={clearAllFilters}
            onRemoveFilter={removeFilter}
            paginationVisibility="desktop-only"
            filtersPanel={
                <BookCatalogFilters
                    filters={filters}
                    stats={stats}
                    categories={categories}
                    years={years}
                    viewMode={viewMode}
                    onViewModeChange={setViewMode}
                />
            }
            deferredData="books"
        >
            <BookCatalogResults books={books} viewMode={viewMode} />

            {books?.next_page_url ? (
                <div className="md:hidden">
                    <div className="flex flex-col items-center gap-3 rounded-2xl border border-dashed bg-muted/20 px-4 py-5 text-center">
                        <p className="text-sm text-muted-foreground">
                            Menampilkan{' '}
                            <span className="font-semibold text-foreground">
                                {books.data.length.toLocaleString('id-ID')}
                            </span>{' '}
                            dari{' '}
                            <span className="font-semibold text-foreground">
                                {(books.total ?? 0).toLocaleString('id-ID')}
                            </span>{' '}
                            judul buku
                        </p>

                        {isAutoLoadEnabled ? (
                            <div className="flex w-full flex-col items-center gap-2">
                                <p className="text-xs text-muted-foreground">
                                    Scroll ke bawah, buku berikutnya akan dimuat
                                    otomatis.
                                </p>
                                <div
                                    ref={autoLoadTriggerRef}
                                    className="h-2 w-full"
                                    aria-hidden="true"
                                />
                                {isLoadingMore ? (
                                    <div className="inline-flex items-center gap-2 text-sm font-medium text-foreground">
                                        <LoaderCircle className="size-4 animate-spin" />
                                        Memuat buku berikutnya...
                                    </div>
                                ) : null}
                            </div>
                        ) : (
                            <Button
                                type="button"
                                size="lg"
                                className="w-full max-w-xs"
                                onClick={enableAutoLoad}
                                disabled={isLoadingMore}
                            >
                                {isLoadingMore ? (
                                    <>
                                        <LoaderCircle className="size-4 animate-spin" />
                                        Memuat buku berikutnya...
                                    </>
                                ) : (
                                    'Muat lebih banyak'
                                )}
                            </Button>
                        )}
                    </div>
                </div>
            ) : isAutoLoadEnabled ? (
                <div className="md:hidden">
                    <div className="rounded-2xl border border-dashed bg-muted/20 px-4 py-4 text-center text-sm text-muted-foreground">
                        Semua buku yang cocok dengan filter ini sudah dimuat.
                    </div>
                </div>
            ) : null}
        </CatalogPage>
    );
}
