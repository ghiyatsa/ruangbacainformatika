import * as React from 'react';
import { SidebarInset } from '@/components/ui/sidebar';
import { cn } from '@/lib/utils';
import type { AppVariant } from '@/types';

type Props = React.ComponentProps<'main'> & {
    variant?: AppVariant | 'full';
};

export function AppContent({ variant = 'sidebar', children, ...props }: Props) {
    if (variant === 'sidebar') {
        return <SidebarInset {...props}>{children}</SidebarInset>;
    }

    return (
        <main
            className={cn(
                'flex h-full w-full flex-1 flex-col',
                variant === 'header' && 'w-full pt-20 sm:pt-28',
                props.className,
            )}
            {...props}
        >
            {children}
        </main>
    );
}
