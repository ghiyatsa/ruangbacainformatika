import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';

interface LibraryPageHeroProps {
    title: ReactNode;
    description: string;
    eyebrow?: ReactNode;
    className?: string;
    contentClassName?: string;
    align?: 'center' | 'left';
}

export function LibraryPageHero({
    title,
    description,
    eyebrow,
    className,
    contentClassName,
    align = 'center',
}: LibraryPageHeroProps) {
    const isCentered = align === 'center';

    return (
        <section
            className={cn(
                'relative -mt-20 overflow-hidden border-b bg-linear-to-br from-primary/5 via-background to-muted/30 pt-34 pb-14 sm:-mt-28 sm:pt-48 sm:pb-20',
                className,
            )}
        >
            <div
                className="pointer-events-none absolute inset-0 -z-10"
                aria-hidden="true"
            >
                <div className="absolute top-[18%] left-1/2 h-56 w-[32rem] -translate-x-1/2 rounded-full bg-primary/12 blur-3xl" />
                <div className="absolute right-[8%] bottom-0 h-48 w-48 rounded-full bg-primary/10 blur-3xl dark:bg-primary/15" />
                <div className="absolute bottom-10 left-[12%] h-40 w-40 rounded-full bg-primary/8 blur-3xl" />
            </div>

            <div
                className={cn(
                    'mx-auto flex max-w-7xl flex-col gap-5 px-6 lg:px-8',
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
