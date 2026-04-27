import { GraduationCap, LayoutGrid, LayoutList } from 'lucide-react';
import type { ReactNode } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

interface CatalogHeaderProps {
    title: string;
    description: ReactNode;
    badgeText?: string;
    viewMode: 'grid' | 'list';
    onViewModeChange: (mode: 'grid' | 'list') => void;
    className?: string;
}

export default function CatalogHeader({
    title,
    description,
    badgeText = 'Koleksi Akademik',
    viewMode,
    onViewModeChange,
    className,
}: CatalogHeaderProps) {
    return (
        <div
            className={cn(
                'flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between',
                className,
            )}
        >
            <div className="flex flex-col gap-3">
                <Badge variant="secondary" className="w-fit">
                    <GraduationCap className="mr-1.5 size-3.5" />
                    {badgeText}
                </Badge>
                <div>
                    <h1 className="text-3xl font-bold tracking-tight sm:text-4xl">
                        {title}
                    </h1>
                    <div className="mt-1.5 text-sm text-muted-foreground">
                        {description}
                    </div>
                </div>
            </div>

            <div className="flex items-center gap-1 self-start rounded-lg border bg-muted/50 p-1">
                <Button
                    variant={viewMode === 'grid' ? 'secondary' : 'ghost'}
                    size="icon"
                    className="size-8"
                    onClick={() => onViewModeChange('grid')}
                >
                    <LayoutGrid className="size-4" />
                    <span className="sr-only">Grid view</span>
                </Button>
                <Button
                    variant={viewMode === 'list' ? 'secondary' : 'ghost'}
                    size="icon"
                    className="size-8"
                    onClick={() => onViewModeChange('list')}
                >
                    <LayoutList className="size-4" />
                    <span className="sr-only">List view</span>
                </Button>
            </div>
        </div>
    );
}
