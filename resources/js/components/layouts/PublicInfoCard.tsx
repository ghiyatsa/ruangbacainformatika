import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';

interface PublicInfoCardProps {
    title: string;
    icon?: LucideIcon;
    children: ReactNode;
    className?: string;
    contentClassName?: string;
    tone?: 'default' | 'accent';
}

export function PublicInfoCard({
    title,
    icon: Icon,
    children,
    className,
    contentClassName,
    tone = 'default',
}: PublicInfoCardProps) {
    return (
        <Card
            className={cn(
                'border-border/60 bg-card/90 shadow-sm backdrop-blur-sm',
                tone === 'accent' &&
                    'border-primary/20 bg-linear-to-br from-primary/8 via-card to-card shadow-primary/5',
                className,
            )}
        >
            <CardHeader className="gap-3">
                <CardTitle className="flex items-center gap-3 text-base font-semibold">
                    {Icon ? (
                        <span className="flex size-10 items-center justify-center rounded-2xl border border-primary/15 bg-primary/8 text-primary">
                            <Icon className="size-4" />
                        </span>
                    ) : null}
                    <span>{title}</span>
                </CardTitle>
            </CardHeader>
            <CardContent
                className={cn(
                    'text-sm leading-7 text-muted-foreground',
                    contentClassName,
                )}
            >
                {children}
            </CardContent>
        </Card>
    );
}
