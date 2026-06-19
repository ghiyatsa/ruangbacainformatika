import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';

interface PublicPageHeroProps {
    title: ReactNode;
    description: string;
    eyebrow?: ReactNode;
    className?: string;
    contentClassName?: string;
    align?: 'center' | 'left';
}

export function PublicPageHero({
    title,
    description,
    eyebrow,
    className,
    contentClassName,
    align = 'center',
}: PublicPageHeroProps) {
    const isCentered = align === 'center';

    return (
        <section
            className={cn(
                'relative overflow-hidden border-b bg-background py-10 sm:py-16',
                className,
            )}
        >
            <div
                className={cn(
                    'mx-auto flex max-w-7xl flex-col gap-5 px-4 sm:px-6 lg:px-8',
                    isCentered ? 'items-center text-center' : 'items-start',
                    contentClassName,
                )}
            >

                {eyebrow ? (
                    <div className="inline-flex items-center gap-2 rounded-full border border-primary/15 bg-background px-3 py-1 text-[11px] font-semibold tracking-[0.18em] text-primary uppercase shadow-sm">
                        {eyebrow}
                    </div>
                ) : null}

                <h1 className="max-w-4xl text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-5xl">
                    {title}
                </h1>
                <p
                    className={cn(
                        'max-w-2xl text-base leading-7 text-muted-foreground sm:text-lg',
                        isCentered && 'mx-auto',
                    )}
                >
                    {description}
                </p>
            </div>
        </section>
    );
}
