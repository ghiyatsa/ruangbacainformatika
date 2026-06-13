import { router } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import DeferredCatalogRescue from './DeferredCatalogRescue';
import type { ReactNode } from 'react';

interface LazyDeferredProps {
    dataKey: string;
    isLoaded: boolean;
    fallback: ReactNode;
    children: ReactNode;
    rescueTitle?: string;
    rescueDescription?: string;
}

export default function LazyDeferred({
    dataKey,
    isLoaded,
    fallback,
    children,
    rescueTitle = 'Gagal memuat data',
    rescueDescription = 'Bagian ini dapat dimuat ulang tanpa memuat ulang seluruh halaman.',
}: LazyDeferredProps) {
    const [hasBeenVisible, setHasBeenVisible] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [hasError, setHasError] = useState(false);
    const containerRef = useRef<HTMLDivElement | null>(null);

    useEffect(() => {
        if (isLoaded) {
            return;
        }

        const element = containerRef.current;

        if (!element) {
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                const [entry] = entries;

                if (entry?.isIntersecting) {
                    setHasBeenVisible(true);
                    observer.disconnect();
                }
            },
            { rootMargin: '250px 0px', threshold: 0.01 }
        );

        observer.observe(element);

        return () => observer.disconnect();
    }, [isLoaded]);

    const loadData = useCallback(() => {
        setIsLoading(true);
        setHasError(false);

        router.reload({
            only: [dataKey],
            onFinish: () => {
                setIsLoading(false);
            },
            onError: () => {
                setHasError(true);
            },
        });
    }, [dataKey]);

    useEffect(() => {
        if (hasBeenVisible && !isLoaded && !isLoading && !hasError) {
            const timer = setTimeout(() => {
                loadData();
            }, 0);

            return () => clearTimeout(timer);
        }
    }, [hasBeenVisible, isLoaded, isLoading, hasError, loadData]);

    useEffect(() => {
        const removeStartListener = router.on('start', (event) => {
            const only = event.detail.visit.only;

            if (only && only.includes(dataKey)) {
                setIsLoading(true);
            }
        });

        const removeFinishListener = router.on('finish', (event) => {
            const only = event.detail.visit.only;

            if (only && only.includes(dataKey)) {
                setIsLoading(false);
            }
        });

        return () => {
            removeStartListener();
            removeFinishListener();
        };
    }, [dataKey]);

    if (!isLoaded) {
        if (hasError) {
            return (
                <div ref={containerRef}>
                    <DeferredCatalogRescue
                        dataKey={dataKey}
                        title={rescueTitle}
                        description={rescueDescription}
                        reloading={isLoading}
                    />
                </div>
            );
        }

        return <div ref={containerRef}>{fallback}</div>;
    }

    return <>{children}</>;
}
