import { GraduationCap, LayoutGrid, LayoutList } from 'lucide-react';
import type { ReactNode } from 'react';
import { Badge } from '@/components/ui/badge';
import {
    ToggleGroup,
    ToggleGroupItem,
} from '@/components/ui/toggle-group';
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
                {badgeText && (
                    <Badge variant="secondary" className="w-fit">
                        <GraduationCap data-icon="inline-start" />
                        {badgeText}
                    </Badge>
                )}
                <div>
                    <h2 className="text-3xl font-bold tracking-tight sm:text-4xl">
                        {title}
                    </h2>
                    <div className="mt-1.5 text-sm text-muted-foreground">
                        {description}
                    </div>
                </div>
            </div>

            <ToggleGroup
                type="single"
                value={viewMode}
                onValueChange={(val) => val && onViewModeChange(val as 'grid' | 'list')}
                variant="outline"
                className="self-start"
            >
                <ToggleGroupItem value="grid" aria-label="Grid view">
                    <LayoutGrid data-icon />
                </ToggleGroupItem>
                <ToggleGroupItem value="list" aria-label="List view">
                    <LayoutList data-icon />
                </ToggleGroupItem>
            </ToggleGroup>
        </div>
    );
}
