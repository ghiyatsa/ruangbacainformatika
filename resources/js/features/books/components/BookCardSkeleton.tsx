import { Skeleton } from '@/components/ui/skeleton';
import { cn } from '@/lib/utils';

interface BookCardSkeletonProps {
    variant?: 'grid' | 'compact';
}

export default function BookCardSkeleton({
    variant = 'grid',
}: BookCardSkeletonProps) {
    const isCompact = variant === 'compact';

    return (
        <div
            className={cn(
                'flex h-full overflow-hidden rounded-2xl border bg-card',
                !isCompact && 'sm:flex-col',
            )}
        >
            <Skeleton
                className={cn(
                    'aspect-3/4 w-32 shrink-0 self-start rounded-none',
                    isCompact
                        ? 'sm:w-36'
                        : 'sm:h-auto sm:w-full sm:self-auto',
                )}
            />

            <div className="flex min-w-0 flex-1 flex-col gap-1.5 p-3 sm:gap-2 sm:p-4">
                <div className="flex min-h-4 gap-1">
                    <Skeleton className="h-4 w-20" />
                    <Skeleton className="h-4 w-8" />
                </div>

                <div className="min-h-[2.5rem] space-y-1">
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-4 w-4/5" />
                </div>

                <div
                    className={cn(
                        'min-h-[0.95rem]',
                        !isCompact && 'sm:min-h-[1.9rem]',
                    )}
                >
                    <div className="space-y-1">
                        <Skeleton className="h-3 w-5/6" />
                        {!isCompact ? (
                            <Skeleton className="hidden h-3 w-2/3 sm:block" />
                        ) : null}
                    </div>
                </div>

                <div className="min-h-4">
                    <Skeleton className="h-3 w-2/3" />
                </div>

                <div className="hidden flex-1 sm:block" />

                <div className="flex min-h-[1.9rem] items-center justify-between border-t pt-2 sm:min-h-8 sm:pt-2.5">
                    <Skeleton className="h-5 w-16 rounded-full" />
                    <div className="flex items-center gap-2">
                        <Skeleton className="h-3 w-8" />
                        <Skeleton className="h-3 w-12" />
                    </div>
                </div>
            </div>
        </div>
    );
}
