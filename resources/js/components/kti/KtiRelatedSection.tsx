import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';

interface KtiRelatedSectionProps {
    title: string;
    description?: string;
    children: ReactNode;
    className?: string;
}

export function KtiRelatedSection({
    title,
    description,
    children,
    className,
}: KtiRelatedSectionProps) {
    return (
        <section className={cn('space-y-5', className)}>
            <div className="-mx-4 border-y border-border/60 my-6 sm:-mx-6 lg:-mx-8">
                <div
                    className="h-6 sm:h-8"
                    style={{
                        backgroundImage:
                            'repeating-linear-gradient(-45deg, var(--color-border) 0, var(--color-border) 1px, transparent 1px, transparent 12px)',
                    }}
                />
            </div>

            <div className="space-y-1.5">
                <h2 className="text-xl font-bold tracking-tight">{title}</h2>
                {description && (
                    <p className="max-w-3xl text-sm leading-relaxed text-muted-foreground sm:text-base">
                        {description}
                    </p>
                )}
            </div>

            {children}
        </section>
    );
}

