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
            <div className="flex max-w-2xl flex-col gap-2">
                <h2 className="text-2xl font-semibold tracking-tight text-foreground sm:text-3xl">
                    {title}
                </h2>
                {subtitle ? (
                    <p className="max-w-xl text-sm leading-6 text-muted-foreground sm:text-base">
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
