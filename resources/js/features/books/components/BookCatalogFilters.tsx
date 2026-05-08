import { router } from '@inertiajs/react';
import { LayoutGrid, LayoutList, Library } from 'lucide-react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import type {
    CategoryItem,
    BookCatalogStats,
    ViewMode,
} from '@/features/books/types';
import booksRoute from '@/routes/books';

interface BookCatalogFiltersProps {
    filters: { search: string; category: string };
    stats: BookCatalogStats;
    categories: CategoryItem[];
    viewMode: ViewMode;
    onViewModeChange: (mode: ViewMode) => void;
}

export function BookCatalogFilters({
    filters,
    stats,
    categories,
    viewMode,
    onViewModeChange,
}: BookCatalogFiltersProps) {
    function handleCategoryChange(slug: string): void {
        router.get(
            booksRoute.index.url(),
            { ...filters, category: slug === 'all' ? '' : slug },
            { preserveScroll: true, replace: true },
        );
    }

    return (
        <div className="flex items-center justify-between gap-4">
            <div className="flex flex-wrap items-center gap-3">
                {/* Collection Count */}
                <div className="hidden items-center gap-2 rounded-lg border bg-muted/30 px-3 py-2 text-xs font-medium text-muted-foreground lg:flex">
                    <Library className="size-3.5" />
                    <span>
                        <strong className="text-foreground">
                            {stats.searchResultsCount?.toLocaleString('id-ID')}
                        </strong>{' '}
                        Hasil
                    </span>
                </div>

                <Separator
                    orientation="vertical"
                    className="hidden h-8 lg:block"
                />

                {/* Category Dropdown */}
                <div className="flex items-center gap-2">
                    <span className="hidden text-xs font-medium text-muted-foreground sm:inline-block">
                        Kategori:
                    </span>
                    <Select
                        value={filters.category || 'all'}
                        onValueChange={handleCategoryChange}
                    >
                        <SelectTrigger className="h-10 w-[180px] rounded-lg shadow-xs sm:w-[220px]">
                            <SelectValue placeholder="Pilih Kategori" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua Kategori</SelectItem>
                            {categories.map((cat) => (
                                <SelectItem key={cat.id} value={cat.slug}>
                                    <div className="flex w-full items-center justify-between gap-4">
                                        <span>{cat.name}</span>
                                        <span className="text-[10px] text-muted-foreground">
                                            ({cat.booksCount})
                                        </span>
                                    </div>
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                <Separator orientation="vertical" className="h-8" />

                {/* View Mode */}
                <ToggleGroup
                    type="single"
                    value={viewMode}
                    onValueChange={(val) =>
                        val && onViewModeChange(val as ViewMode)
                    }
                    variant="outline"
                    className="gap-0 -space-x-px"
                >
                    <ToggleGroupItem
                        value="grid"
                        aria-label="Tampilan grid"
                        className="rounded-r-none px-3"
                    >
                        <LayoutGrid className="size-4" />
                    </ToggleGroupItem>
                    <ToggleGroupItem
                        value="list"
                        aria-label="Tampilan daftar"
                        className="rounded-l-none px-3"
                    >
                        <LayoutList className="size-4" />
                    </ToggleGroupItem>
                </ToggleGroup>
            </div>
        </div>
    );
}
