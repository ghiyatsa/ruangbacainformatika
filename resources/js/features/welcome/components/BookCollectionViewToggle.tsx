import { LayoutGrid, LayoutList } from 'lucide-react';
import { Button } from '@/components/ui/button';

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
        <div className="hidden items-center gap-1 rounded-xl border border-border/60 bg-transparent p-1 sm:inline-flex">
            <Button
                type="button"
                variant="ghost"
                size="sm"
                className={`h-9 rounded-lg px-3 transition-all duration-200 ${
                    viewMode === 'grid'
                        ? 'bg-muted text-foreground'
                        : 'text-muted-foreground hover:text-foreground'
                }`}
                onClick={() => onChange('grid')}
                aria-label="Tampilan grid"
                title="Tampilan grid"
            >
                <LayoutGrid className="size-4" />
                <span className="text-xs sm:hidden">Grid</span>
            </Button>
            <Button
                type="button"
                variant="ghost"
                size="sm"
                className={`h-9 rounded-lg px-3 transition-all duration-200 ${
                    viewMode === 'list'
                        ? 'bg-muted text-foreground'
                        : 'text-muted-foreground hover:text-foreground'
                }`}
                onClick={() => onChange('list')}
                aria-label="Tampilan daftar"
                title="Tampilan daftar"
            >
                <LayoutList className="size-4" />
                <span className="text-xs sm:hidden">List</span>
            </Button>
        </div>
    );
}
