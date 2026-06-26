import { WhenVisible } from '@inertiajs/react';
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
}: LazyDeferredProps) {
    if (!isLoaded) {
        return (
            <WhenVisible data={dataKey} fallback={fallback} buffer={250}>
                {children}
            </WhenVisible>
        );
    }

    return <>{children}</>;
}
