import { router } from '@inertiajs/react';
import { LayoutGrid, LayoutList, Library } from 'lucide-react';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
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
    BookCatalogFilters as FilterTypes,
} from '@/features/books/types';
import booksRoute from '@/routes/books';

interface BookCatalogFiltersProps {
    filters: FilterTypes;
    stats: BookCatalogStats;
    categories: CategoryItem[];
    years: number[];
    viewMode: ViewMode;
    onViewModeChange: (mode: ViewMode) => void;
}

export function BookCatalogFilters({
    filters,
    stats,
    categories,
    years,
    viewMode,
    onViewModeChange,
}: BookCatalogFiltersProps) {
    function applyFilters(overrides: Partial<FilterTypes>): void {
        const next = { ...filters, ...overrides };
        router.get(
            booksRoute.index.url(),
            {
                ...next,
                category: next.category === 'all' ? '' : next.category,
            },
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
                        onValueChange={(val) => applyFilters({ category: val })}
                    >
                        <SelectTrigger className="h-10 w-[160px] rounded-lg shadow-xs sm:w-[200px]">
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

                {/* Year Select */}
                <div className="flex items-center gap-2">
                    <span className="hidden text-xs font-medium text-muted-foreground sm:inline-block">
                        Tahun:
                    </span>
                    <Select
                        value={filters.year ? String(filters.year) : 'all'}
                        onValueChange={(val) =>
                            applyFilters({
                                year: val === 'all' ? null : Number(val),
                            })
                        }
                    >
                        <SelectTrigger className="h-10 w-28 rounded-lg shadow-xs sm:w-32">
                            <SelectValue placeholder="Tahun" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua</SelectItem>
                            {years.map((y) => (
                                <SelectItem key={y} value={String(y)}>
                                    {y}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                <Separator
                    orientation="vertical"
                    className="hidden h-8 md:block"
                />

                {/* Featured Toggle */}
                <div className="flex items-center gap-2">
                    <Checkbox
                        id="featured-filter"
                        checked={filters.featured}
                        onCheckedChange={(checked) =>
                            applyFilters({ featured: !!checked })
                        }
                    />
                    <Label
                        htmlFor="featured-filter"
                        className="cursor-pointer text-xs font-medium text-muted-foreground select-none"
                    >
                        Unggulan
                    </Label>
                </div>

                {/* Availability Toggle */}
                <div className="flex items-center gap-2">
                    <Checkbox
                        id="availability-filter"
                        checked={filters.availability}
                        onCheckedChange={(checked) =>
                            applyFilters({ availability: !!checked })
                        }
                    />
                    <Label
                        htmlFor="availability-filter"
                        className="cursor-pointer text-xs font-medium text-muted-foreground select-none"
                    >
                        Tersedia
                    </Label>
                </div>

                <Separator
                    orientation="vertical"
                    className="hidden h-8 md:block"
                />

                {/* View Mode */}
                <ToggleGroup
                    type="single"
                    value={viewMode}
                    onValueChange={(val) =>
                        val && onViewModeChange(val as ViewMode)
                    }
                    variant="outline"
                    className="hidden gap-0 -space-x-px md:flex"
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
