import { router } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import type { PaginationData } from '@/types/pagination';

interface MobileProgressivePaginationProps<T> {
    data?: PaginationData<T>;
    propKey: string;
    resourceLabel: string;
}

export function MobileProgressivePagination<T>({
    data,
    propKey,
    resourceLabel,
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
                            <p className="text-xs text-muted-foreground">
                                Scroll ke bawah, data berikutnya akan dimuat
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
                                    Memuat {resourceLabel} berikutnya...
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
                                    Memuat {resourceLabel} berikutnya...
                                </>
                            ) : (
                                'Muat lebih banyak'
                            )}
                        </Button>
                    )}
                </div>
            ) : (
                <div className="rounded-2xl border border-dashed bg-muted/20 px-4 py-4 text-center text-sm text-muted-foreground">
                    Semua {resourceLabel} yang cocok dengan filter ini sudah
                    dimuat.
                </div>
            )}
        </div>
    );
}
