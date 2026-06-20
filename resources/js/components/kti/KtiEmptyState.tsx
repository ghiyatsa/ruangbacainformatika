import type { LucideIcon } from 'lucide-react';

interface KtiEmptyStateProps {
    icon: LucideIcon;
    title: string;
    description?: string;
}

export function KtiEmptyState({
    icon: Icon,
    title,
    description,
}: KtiEmptyStateProps) {
    return (
        <div className="rounded-2xl border border-dashed bg-muted/30 p-10 text-center">
            <Icon className="mx-auto mb-3 size-10 text-muted-foreground/40" />
            <p className="text-sm text-muted-foreground">
                {title}
            </p>
            {description ? (
                <p className="mt-1 text-sm text-muted-foreground">
                    {description}
                </p>
            ) : null}
        </div>
    );
}
