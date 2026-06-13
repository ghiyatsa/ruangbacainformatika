import type { ReactNode } from 'react';

interface KtiDetailItemProps {
    icon: ReactNode;
    label: string;
    value: ReactNode;
}

export function KtiDetailItem({
    icon,
    label,
    value,
}: KtiDetailItemProps) {
    return (
        <div className="group flex items-start gap-3 rounded-xl p-3 transition-colors hover:bg-muted/50">
            <div className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                {icon}
            </div>
            <div className="min-w-0">
                <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                    {label}
                </p>
                <div className="mt-0.5 truncate text-sm font-semibold text-foreground">
                    {value}
                </div>
            </div>
        </div>
    );
}

