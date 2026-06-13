import * as React from 'react';

const SonnerToaster = React.lazy(async () => {
    const module = await import('@/components/ui/sonner');

    return { default: module.Toaster };
});

export function LazyToaster() {
    return (
        <React.Suspense fallback={null}>
            <SonnerToaster />
        </React.Suspense>
    );
}
