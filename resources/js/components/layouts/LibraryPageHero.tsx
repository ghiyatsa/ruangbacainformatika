import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

interface LibraryPageHeroProps {
    title: ReactNode;
    description: string;
    className?: string;
}

export function LibraryPageHero({
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
                <h1 className="text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-5xl">
                    {title}
                </h1>
                <p className="mx-auto mt-4 max-w-2xl text-base text-muted-foreground sm:text-lg">
                    {description}
                </p>
            </div>
        </section>
    );
}
