import { BookOpen } from 'lucide-react';
import { Skeleton } from '@/components/ui/skeleton';

/**
 * Skeleton loader for BookListItem — uses shadcn Skeleton to
 * mirror the real list item's layout.
 */
export default function BookListItemSkeleton() {
    return (
        <div className="flex items-center gap-4 px-4 py-3.5 sm:gap-5 sm:px-5 sm:py-4">
            {/* Thumbnail placeholder */}
            <Skeleton className="h-18 w-12 shrink-0 rounded-lg sm:h-20 sm:w-14" />

            {/* Content placeholder */}
            <div className="flex min-w-0 flex-1 flex-col gap-2">
                <div className="flex items-center gap-1.5">
                    <Skeleton className="h-4 w-16" />
                </div>
                <Skeleton className="h-5 w-3/4" />
                <Skeleton className="h-3 w-1/2" />
            </div>

            {/* Status placeholder */}
            <div className="shrink-0">
                <Skeleton className="h-5 w-16 rounded-full" />
            </div>

            {/* Page count — desktop */}
            <div className="hidden shrink-0 items-center gap-1 sm:flex">
                <BookOpen className="size-3 text-muted-foreground/40" />
                <Skeleton className="h-3 w-12" />
            </div>
        </div>
    );
}
