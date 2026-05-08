import BookCardSkeleton from '@/features/books/components/BookCardSkeleton';
import BookListItemSkeleton from '@/features/books/components/BookListItemSkeleton';
import type { ViewMode } from '@/features/books/types';

/**
 * Shared loading skeleton for the book grid/list.
 * Replaces the identical ResultsSkeleton in catalog.tsx
 * and CategorySkeleton in books/category.tsx.
 */
export function BookGridSkeleton({ viewMode }: { viewMode: ViewMode }) {
    return (
        <div className="flex flex-col gap-4">
            {viewMode === 'list' ? (
                <div className="grid grid-cols-1 gap-3 lg:grid-cols-2">
                    {Array.from({ length: 8 }).map((_, i) => (
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
