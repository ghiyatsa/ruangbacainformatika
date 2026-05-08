import { Deferred, router } from '@inertiajs/react';
import { Search, X } from 'lucide-react';
import { useState } from 'react';
import { CatalogPageLayout } from '@/components/catalog/CatalogPageLayout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { BookCatalogFilters } from '@/features/books/components/BookCatalogFilters';
import { BookCatalogHeader } from '@/features/books/components/BookCatalogHeader';
import { BookCatalogResults } from '@/features/books/components/BookCatalogResults';
import { BookGridSkeleton } from '@/features/books/components/BookGridSkeleton';
import type {
    BookCatalogPageProps,
    ViewMode,
} from '@/features/books/types';
import booksRoute from '@/routes/books';

export default function BookCatalogPage({
    filters,
    stats,
    categories,
    books,
}: BookCatalogPageProps) {
    const [viewMode, setViewMode] = useState<ViewMode>('grid');

    function clearAllFilters(): void {
        router.get(
            booksRoute.index.url(),
            {},
            { preserveScroll: true, replace: true },
        );
    }

    return (
        <CatalogPageLayout
            title="Katalog Buku"
            header={<BookCatalogHeader total={stats.booksCount ?? 0} />}
        >
            <div className="flex flex-col gap-6">
                <BookCatalogFilters
                    filters={filters}
                    stats={stats}
                    categories={categories}
                    viewMode={viewMode}
                    onViewModeChange={setViewMode}
                />

                {/* Active Search Badge */}
                {filters.search && (
                    <div className="flex items-center gap-2">
                        <Badge
                            variant="secondary"
                            className="gap-1.5 py-1.5 pr-2 pl-2.5"
                        >
                            <Search className="size-3 text-muted-foreground" />
                            <span className="text-muted-foreground">
                                Hasil pencarian:
                            </span>
                            &ldquo;{filters.search}&rdquo;
                            <button
                                onClick={clearAllFilters}
                                className="ml-1 rounded-full p-0.5 transition-colors hover:bg-muted"
                            >
                                <X className="size-3" />
                            </button>
                        </Badge>
                        <Button
                            variant="ghost"
                            size="sm"
                            className="h-8 text-xs text-muted-foreground"
                            onClick={clearAllFilters}
                        >
                            Hapus semua filter
                        </Button>
                    </div>
                )}
            </div>

            {/* Results */}
            <Deferred
                data="books"
                fallback={<BookGridSkeleton viewMode={viewMode} />}
            >
                <BookCatalogResults
                    books={books}
                    viewMode={viewMode}
                />
            </Deferred>
        </CatalogPageLayout>
    );
}
