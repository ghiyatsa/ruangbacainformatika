import { Search, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

interface FilterSidebarProps {
    searchValue: string;
    onSearchChange: (value: string) => void;
    onClearFilters: () => void;
    hasActiveFilters: boolean;
    className?: string;
    onFilterApplied?: () => void;
}

export default function FilterSidebar({
    searchValue,
    onSearchChange,
    onClearFilters,
    hasActiveFilters,
    className,
    onFilterApplied,
}: FilterSidebarProps) {
    return (
        <aside className={cn('flex flex-col gap-4', className)}>
            {/* Search */}
            <div className="relative">
                <Search className="pointer-events-none absolute top-1/2 left-3 size-3.5 -translate-y-1/2 text-muted-foreground" />
                <Input
                    id="catalog-search"
                    value={searchValue}
                    onChange={(e) => onSearchChange(e.target.value)}
                    placeholder="Cari judul, penulis…"
                    className="h-9 pl-9 pr-8 text-sm"
                />
                {searchValue && (
                    <button
                        type="button"
                        onClick={() => {
                            onSearchChange('');
                            onFilterApplied?.();
                        }}
                        className="absolute top-1/2 right-2.5 -translate-y-1/2 text-muted-foreground transition-colors hover:text-foreground"
                        aria-label="Hapus pencarian"
                    >
                        <X className="size-3.5" />
                    </button>
                )}
            </div>

            {/* Clear all */}
            {hasActiveFilters && (
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    className="w-full justify-start text-muted-foreground"
                    onClick={() => {
                        onClearFilters();
                        onFilterApplied?.();
                    }}
                >
                    <X data-icon="inline-start" />
                    Hapus semua filter
                </Button>
            )}
        </aside>
    );
}
