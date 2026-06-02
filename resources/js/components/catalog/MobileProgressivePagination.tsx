import { router } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import type { ReactNode } from 'react';
import type { PaginationData } from '@/types/pagination';

interface MobileProgressivePaginationProps<T> {
    data?: PaginationData<T>;
    propKey: string;
    resourceLabel: string;
    loadingFallback?: ReactNode;
}

export function MobileProgressivePagination<T>({
    data,
    propKey,
    resourceLabel,
    loadingFallback,
}: MobileProgressivePaginationProps<T>) {
    const [isLoadingMore, setIsLoadingMore] = useState(false);
    const [isAutoLoadEnabled, setIsAutoLoadEnabled] = useState(false);
    const autoLoadTriggerRef = useRef<HTMLDivElement | null>(null);

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
        <div className="md:hidden">
            {data?.next_page_url ? (
                <div className="flex flex-col items-center gap-3 rounded-2xl border border-dashed bg-muted/20 px-4 py-5 text-center">
                    <p className="text-sm text-muted-foreground">
                        Menampilkan{' '}
                        <span className="font-semibold text-foreground">
                            {data.data.length.toLocaleString('id-ID')}
                        </span>{' '}
                        dari{' '}
                        <span className="font-semibold text-foreground">
                            {(data.total ?? 0).toLocaleString('id-ID')}
                        </span>{' '}
                        {resourceLabel}
                    </p>

                    {isAutoLoadEnabled ? (
                        <div className="flex w-full flex-col items-center gap-2">
                            <div
                                ref={autoLoadTriggerRef}
                                className="h-2 w-full"
                                aria-hidden="true"
                            />
                            {isLoadingMore ? loadingSkeleton : null}
                        </div>
                    ) : (
                        <Button
                            type="button"
                            size="lg"
                            className="w-full max-w-xs"
                            onClick={enableAutoLoad}
                            disabled={isLoadingMore}
                        >
                            Tampilkan lebih banyak
                        </Button>
                    )}

                    {!isAutoLoadEnabled && isLoadingMore
                        ? loadingSkeleton
                        : null}
                </div>
            ) : (
                <div className="rounded-2xl border border-dashed bg-muted/20 px-4 py-4 text-center text-sm text-muted-foreground">
                    Semua daftar telah ditampilkan.
                </div>
            )}
        </div>
    );
}
