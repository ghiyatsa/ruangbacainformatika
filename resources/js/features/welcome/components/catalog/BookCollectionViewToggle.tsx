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
        <div className="inline-flex items-center gap-1 rounded-xl border bg-muted/50 p-1">
            <Button
                type="button"
                variant={viewMode === 'grid' ? 'secondary' : 'ghost'}
                size="sm"
                className="h-9 rounded-lg px-3"
                onClick={() => onChange('grid')}
                aria-label="Tampilan grid"
                title="Tampilan grid"
            >
                <LayoutGrid className="size-4" />
                <span className="text-xs sm:hidden">Grid</span>
            </Button>
            <Button
                type="button"
                variant={viewMode === 'list' ? 'secondary' : 'ghost'}
                size="sm"
                className="h-9 rounded-lg px-3"
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
