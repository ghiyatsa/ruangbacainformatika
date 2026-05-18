import { LayoutGrid, LayoutList } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

export type BookCollectionViewMode = 'grid' | 'list';

interface BookCollectionViewToggleProps {
    viewMode: BookCollectionViewMode;
    onChange: (mode: BookCollectionViewMode) => void;
}

export default function BookCollectionViewToggle({
    viewMode,
    onChange,
}: BookCollectionViewToggleProps) {
    return (
        <TooltipProvider>
            <div className="hidden items-center gap-1 rounded-lg border bg-muted/50 p-1 sm:flex">
                <Tooltip>
                    <TooltipTrigger asChild>
                        <Button
                            variant={viewMode === 'grid' ? 'secondary' : 'ghost'}
                            size="icon"
                            className="size-8"
                            onClick={() => onChange('grid')}
                            aria-label="Tampilan grid"
                        >
                            <LayoutGrid className="size-4" />
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Tampilan Grid</TooltipContent>
                </Tooltip>
                <Tooltip>
                    <TooltipTrigger asChild>
                        <Button
                            variant={viewMode === 'list' ? 'secondary' : 'ghost'}
                            size="icon"
                            className="size-8"
                            onClick={() => onChange('list')}
                            aria-label="Tampilan daftar"
                        >
                            <LayoutList className="size-4" />
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Tampilan Daftar</TooltipContent>
                </Tooltip>
            </div>
        </TooltipProvider>
    );
}
