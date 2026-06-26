import { router } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';
import type { PaginationData } from '@/types/pagination';

type ProgressivePaginationMode = 'auto' | 'manual-then-auto';

interface CatalogMobilePaginationProps<T> {
    data?: PaginationData<T>;
    propKey: string;
    resourceLabel: string;
    loadingFallback?: ReactNode;
    className?: string;
    mode?: ProgressivePaginationMode;
    buttonLabel?: string;
    completeLabel?: string;
}

export function CatalogMobilePagination<T>({
    data,
    propKey,
    resourceLabel,
    loadingFallback,
    className,
    mode = 'manual-then-auto',
    buttonLabel = 'Tampilkan lebih banyak',
    completeLabel = 'Semua daftar telah ditampilkan.',
}: CatalogMobilePaginationProps<T>) {
    const [isLoadingMore, setIsLoadingMore] = useState(false);
    const [isAutoLoadEnabled, setIsAutoLoadEnabled] = useState(mode === 'auto');
    const autoLoadTriggerRef = useRef<HTMLDivElement | null>(null);

    const [prevMode, setPrevMode] = useState(mode);
    const [prevPage, setPrevPage] = useState(data?.current_page);

    if (mode !== prevMode || data?.current_page !== prevPage) {
        setPrevMode(mode);
        setPrevPage(data?.current_page);

        if (mode !== prevMode) {
            setIsAutoLoadEnabled(mode === 'auto');
        }
    }

    const loadMore = useCallback((): void => {
        if (!data?.next_page_url || isLoadingMore) {
            return;
        }

        router.visit(data.next_page_url, {
            only: [propKey],
            preserveScroll: true,
            preserveState: true,
            preserveUrl: true,
            onStart: () => setIsLoadingMore(true),
            onFinish: () => setIsLoadingMore(false),
        });
    }, [data, isLoadingMore, propKey]);

    function enableAutoLoad(): void {
        if (isAutoLoadEnabled) {
            return;
        }

        setIsAutoLoadEnabled(true);
        loadMore();
    }

    useEffect(() => {
        if (!isAutoLoadEnabled || !data?.next_page_url || isLoadingMore) {
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

                loadMore();
            },
            {
                rootMargin: '240px 0px',
                threshold: 0.1,
            },
        );

        observer.observe(triggerElement);

        return () => observer.disconnect();
    }, [data?.next_page_url, isAutoLoadEnabled, isLoadingMore, loadMore]);

    if (!data?.next_page_url && !isAutoLoadEnabled) {
        return null;
    }

    const loadingSkeleton = loadingFallback ?? (
        <div className="w-full space-y-2" aria-hidden="true">
            <Skeleton className="h-10 w-full rounded-xl" />
            <div className="grid grid-cols-3 gap-2">
                <Skeleton className="h-3 w-full" />
                <Skeleton className="h-3 w-full" />
                <Skeleton className="h-3 w-full" />
            </div>
        </div>
    );

    return (
        <div className={cn(className, isAutoLoadEnabled ? '-mt-8' : '')}>
            {data?.next_page_url ? (
                <div className="flex flex-col items-center gap-3 text-center">
                    {isAutoLoadEnabled ? (
                        <div className="flex w-full flex-col items-center">
                            <div
                                ref={autoLoadTriggerRef}
                                className="h-px w-full"
                                aria-label={`Pemicu lazy loading ${resourceLabel}`}
                                aria-hidden="true"
                            />
                            {isLoadingMore ? (
                                <div className="w-full mt-3 sm:mt-4">
                                    {loadingSkeleton}
                                </div>
                            ) : null}
                        </div>
                    ) : (
                        <Button
                            type="button"
                            size="lg"
                            className="min-w-56"
                            onClick={enableAutoLoad}
                            disabled={isLoadingMore}
                        >
                            {buttonLabel}
                        </Button>
                    )}

                    {!isAutoLoadEnabled && isLoadingMore ? (
                        <div className="w-full mt-3">
                            {loadingSkeleton}
                        </div>
                    ) : null}
                </div>
            ) : (
                <div className="py-4 text-center text-sm text-muted-foreground">
                    {completeLabel}
                </div>
            )}
        </div>
    );
}
