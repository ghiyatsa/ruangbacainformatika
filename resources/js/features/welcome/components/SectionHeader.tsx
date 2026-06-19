import type { ReactNode } from 'react';

interface SectionHeaderProps {
    title: string;
    action?: ReactNode;
}

export default function SectionHeader({
    title,
    action,
}: SectionHeaderProps) {
    return (
        <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div className="flex max-w-2xl flex-col gap-2">
                <h2 className="text-2xl font-semibold tracking-tight text-foreground sm:text-3xl">
                    {title}
                </h2>
            </div>

            {action ? (
                <div className="self-start sm:self-auto">{action}</div>
            ) : null}
        </div>
    );
}
