import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';

interface PublicPageSectionProps {
    title: string;
    description?: string;
    eyebrow?: ReactNode;
    action?: ReactNode;
    children: ReactNode;
    className?: string;
    contentClassName?: string;
}

export function PublicPageSection({
    title,
    description,
    eyebrow,
    action,
    children,
    className,
    contentClassName,
}: PublicPageSectionProps) {
    return (
        <section className={cn('space-y-6', className)}>
            <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div className="max-w-3xl space-y-3">
                    {eyebrow ? (
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/15 bg-primary/5 px-3 py-1 text-[11px] font-semibold tracking-[0.18em] text-primary uppercase">
                            {eyebrow}
                        </div>
                    ) : null}

                    <div className="space-y-2">
                        <h2 className="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            {title}
                        </h2>
                        {description ? (
                            <p className="text-sm leading-7 text-muted-foreground sm:text-base">
                                {description}
                            </p>
                        ) : null}
                    </div>
                </div>

                {action ? <div className="shrink-0">{action}</div> : null}
            </div>

            <div className={cn('space-y-6', contentClassName)}>{children}</div>
        </section>
    );
}
