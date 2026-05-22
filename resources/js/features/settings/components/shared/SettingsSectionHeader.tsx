import { cn } from '@/lib/utils';
import type { LucideIcon } from 'lucide-react';

interface SettingsSectionHeaderProps {
    title: string;
    description: string;
    icon?: LucideIcon;
    iconClassName?: string;
}

export function SettingsSectionHeader({
    title,
    description,
    icon: Icon,
    iconClassName,
}: SettingsSectionHeaderProps) {
    return (
        <div className="flex items-start gap-3">
            {Icon ? (
                <div
                    className={cn(
                        'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary',
                        iconClassName,
                    )}
                >
                    <Icon className="h-4 w-4" />
                </div>
            ) : null}
            <div>
                <h2 className="text-base font-semibold">{title}</h2>
                <p className="mt-0.5 text-sm text-muted-foreground">
                    {description}
                </p>
            </div>
        </div>
    );
}
