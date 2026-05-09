import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

interface LibraryPageHeroProps {
    badge: ReactNode;
    title: ReactNode;
    description: string;
    className?: string;
}

export function LibraryPageHero({
    badge,
    title,
    description,
    className,
}: LibraryPageHeroProps) {
    return (
        <section
            className={cn(
                'relative -mt-20 border-b bg-linear-to-br from-primary/5 via-background to-muted/30 pt-34 pb-14 sm:-mt-28 sm:pt-48 sm:pb-20',
                className,
            )}
        >
            <div className="mx-auto max-w-7xl px-6 text-center lg:px-8">
                <div className="mb-6 inline-flex items-center gap-2 rounded-full border bg-card px-4 py-1.5 text-sm font-medium text-muted-foreground shadow-sm">
                    {badge}
                </div>
                <h1 className="text-4xl font-extrabold tracking-tight lg:text-5xl">
                    {title}
                </h1>
                <p className="mx-auto mt-4 max-w-2xl text-lg text-muted-foreground">
                    {description}
                </p>
            </div>
        </section>
    );
}
