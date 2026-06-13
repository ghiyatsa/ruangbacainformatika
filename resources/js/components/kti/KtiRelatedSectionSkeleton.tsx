import { KtiCardSkeleton } from '@/components/kti/KtiCardSkeleton';
import { Skeleton } from '@/components/ui/skeleton';
import BookCardSkeleton from '@/features/books/components/BookCardSkeleton';

interface KtiRelatedSectionSkeletonProps {
    variant?: 'academic' | 'book';
}

export function KtiRelatedSectionSkeleton({
    variant = 'academic',
}: KtiRelatedSectionSkeletonProps) {
    const isBook = variant === 'book';

    return (
        <div className="space-y-5">
            <div className="space-y-2">
                <Skeleton className="h-6 w-44" />
                <Skeleton className="h-4 w-full max-w-2xl" />
                <Skeleton className="h-4 w-4/5 max-w-xl" />
            </div>

            <div
                className={
                    isBook
                        ? 'grid gap-4 lg:grid-cols-2'
                        : 'grid gap-4 md:grid-cols-2 xl:grid-cols-3'
                }
            >
                {Array.from({ length: 3 }).map((_, index) => (
                    <div key={index}>
                        {isBook ? (
                            <BookCardSkeleton variant="compact" />
                        ) : (
                            <KtiCardSkeleton />
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
}

