import type { ReactNode } from 'react';

interface SectionHeaderProps {
    title: string;
    subtitle?: string;
    action?: ReactNode;
}

export default function SectionHeader({
    title,
    subtitle,
    action,
}: SectionHeaderProps) {
    return (
        <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div className="flex max-w-2xl flex-col gap-1.5">
                <h2 className="text-2xl font-bold tracking-tight sm:text-3xl">
                    {title}
                </h2>
                {subtitle ? (
                    <p className="text-sm leading-relaxed text-muted-foreground">
                        {subtitle}
                    </p>
                ) : null}
            </div>

            {action ? (
                <div className="self-start sm:self-auto">{action}</div>
            ) : null}
        </div>
    );
}
