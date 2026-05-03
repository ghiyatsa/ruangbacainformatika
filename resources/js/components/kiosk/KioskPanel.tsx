import type { ReactNode } from 'react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';

export function KioskPanel({
    title,
    description,
    children,
    className,
    icon,
}: {
    title: string;
    description?: string;
    children: ReactNode;
    className?: string;
    icon?: ReactNode;
}) {
    return (
        <Card className={cn('w-full border-border/60 shadow-md', className)}>
            <CardHeader className="pb-4">
                <div className="flex items-start gap-3">
                    {icon && (
                        <div className="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            {icon}
                        </div>
                    )}
                    <div>
                        <CardTitle className="text-lg">{title}</CardTitle>
                        {description ? (
                            <CardDescription className="mt-0.5">
                                {description}
                            </CardDescription>
                        ) : null}
                    </div>
                </div>
            </CardHeader>
            <Separator className="mb-0" />
            <CardContent className="pt-6">{children}</CardContent>
        </Card>
    );
}
