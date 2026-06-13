import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';

interface RelatedCatalogSectionProps {
    title: string;
    description: string;
    children: ReactNode;
    className?: string;
}

export function RelatedCatalogSection({
    title,
    description,
    children,
    className,
}: RelatedCatalogSectionProps) {
    return (
        <section className={cn('space-y-5', className)}>
            <div className="space-y-1.5">
                <h2 className="text-xl font-bold tracking-tight">{title}</h2>
                <p className="max-w-3xl text-sm leading-relaxed text-muted-foreground sm:text-base">
                    {description}
                </p>
            </div>

            {children}
        </section>
    );
}
