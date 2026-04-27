import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Search, X } from 'lucide-react';

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
}

export default function FilterSidebar({
    searchValue,
    onSearchChange,
    categories,
    activeCategory,
    onCategoryChange,
    onClearFilters,
    hasActiveFilters,
}: FilterSidebarProps) {
    return (
        <aside className="hidden w-56 shrink-0 lg:block">
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
                                onClick={() => onCategoryChange('')}
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
                                onClick={() => onCategoryChange(cat.slug)}
                                className={`group flex items-center justify-between rounded-lg px-3 py-1.5 text-left text-sm transition-colors ${
                                    activeCategory === cat.slug
                                        ? 'bg-primary text-primary-foreground font-medium'
                                        : 'text-foreground hover:bg-muted'
                                }`}
                            >
                                <span className="truncate">{cat.name}</span>
                                {cat.booksCount !== undefined && (
                                    <span className={`text-[10px] ${
                                        activeCategory === cat.slug ? 'text-primary-foreground/80' : 'text-muted-foreground'
                                    }`}>
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
                        variant="ghost"
                        size="sm"
                        className="w-full justify-start gap-2 text-muted-foreground"
                        onClick={onClearFilters}
                    >
                        <X className="size-3.5" />
                        Hapus semua filter
                    </Button>
                )}
            </div>
        </aside>
    );
}
