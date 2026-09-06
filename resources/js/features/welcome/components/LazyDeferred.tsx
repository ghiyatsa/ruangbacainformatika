import type { ReactNode } from 'react';

interface LazyDeferredProps {
    dataKey?: string;
    isLoaded?: boolean;
    fallback?: ReactNode;
    children: ReactNode;
    rescueTitle?: string;
    rescueDescription?: string;
}

export default function LazyDeferred({ children }: LazyDeferredProps) {
    return <>{children}</>;
}
