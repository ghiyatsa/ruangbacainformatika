import { Search, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

interface FilterSidebarProps {
    searchValue: string;
    onSearchChange: (value: string) => void;
    categories: {
        id: number;
        name: string;
        slug: string;
        booksCount?: number;
    }[];
    activeCategory: string;
    onCategoryChange: (slug: string) => void;
    onClearFilters: () => void;
    hasActiveFilters: boolean;
    className?: string;
    onFilterApplied?: () => void;
}

export default function FilterSidebar({
    searchValue,
    onSearchChange,
    categories,
    activeCategory,
    onCategoryChange,
    onClearFilters,
    hasActiveFilters,
    className = 'hidden w-56 shrink-0 lg:block',
    onFilterApplied,
}: FilterSidebarProps) {
    return (
        <aside className={className}>
            <div className="sticky top-24 flex flex-col gap-6">
                {/* Search */}
                <div className="relative">
                    <Search className="pointer-events-none absolute top-1/2 left-3 size-3.5 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        id="catalog-search"
                        value={searchValue}
                        onChange={(e) => onSearchChange(e.target.value)}
                        placeholder="Cari buku…"
                        className="h-9 pl-9 pr-8 text-sm"
                    />
                    {searchValue && (
                        <button
                            type="button"
                            onClick={() => onSearchChange('')}
                            className="absolute top-1/2 right-2.5 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            aria-label="Hapus pencarian"
                        >
                            <X className="size-3.5" />
                        </button>
                    )}
                </div>

                {/* Category filter */}
                <div>
                    <div className="mb-2.5 flex items-center justify-between">
                        <span className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                            Kategori
                        </span>
                        {activeCategory && (
                            <button
                                type="button"
                                onClick={() => {
                                    onCategoryChange('');
                                    onFilterApplied?.();
                                }}
                                className="text-[11px] text-muted-foreground hover:text-foreground"
                            >
                                Reset
                            </button>
                        )}
                    </div>
                    <div className="flex flex-col gap-0.5">
                        {categories.map((cat) => (
                            <button
                                key={cat.id}
                                type="button"
                                onClick={() => {
                                    onCategoryChange(cat.slug);
                                    onFilterApplied?.();
                                }}
                                aria-pressed={activeCategory === cat.slug}
                                className={`group flex items-center justify-between rounded-lg px-3 py-1.5 text-left text-sm transition-colors ${
                                    activeCategory === cat.slug
                                        ? 'bg-primary text-primary-foreground font-medium'
                                        : 'text-foreground hover:bg-muted'
                                }`}
                            >
                                <span className="truncate">{cat.name}</span>
                                {cat.booksCount !== undefined && (
                                    <span
                                        className={`flex min-w-5 items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-medium transition-colors ${
                                            activeCategory === cat.slug
                                                ? 'bg-primary-foreground/20 text-primary-foreground'
                                                : 'bg-muted-foreground/10 text-muted-foreground group-hover:bg-muted-foreground/20'
                                        }`}
                                    >
                                        {cat.booksCount}
                                    </span>
                                )}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Clear all */}
                {hasActiveFilters && (
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        className="w-full justify-start gap-2 text-muted-foreground"
                        onClick={() => {
                            onClearFilters();
                            onFilterApplied?.();
                        }}
                    >
                        <X className="size-3.5" />
                        Hapus semua filter
                    </Button>
                )}
            </div>
        </aside>
    );
}
