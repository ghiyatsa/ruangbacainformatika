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
import booksRoute from '@/routes/books';
import type {
    CategoryItem,
    BookCatalogStats,
    ViewMode,
    BookCatalogFilters as FilterTypes,
} from '@/features/books/types';

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
    const totalResults = stats.searchResultsCount?.toLocaleString('id-ID') ?? '0';

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
        <div className="flex flex-col gap-4">
            <div className="flex items-center justify-between gap-3">
                <div className="inline-flex items-center gap-2 rounded-xl border bg-muted/30 px-3 py-2 text-xs font-medium text-muted-foreground">
                    <Library className="size-3.5" />
                    <span>
                        <strong className="text-foreground">{totalResults}</strong>{' '}
                        hasil
                    </span>
                </div>

                <ToggleGroup
                    type="single"
                    value={viewMode}
                    onValueChange={(val) =>
                        val && onViewModeChange(val as ViewMode)
                    }
                    variant="outline"
                    className="hidden sm:flex gap-1"
                    spacing={1}
                >
                    <ToggleGroupItem
                        value="grid"
                        aria-label="Tampilan grid"
                        className="rounded-lg px-3"
                    >
                        <LayoutGrid className="size-4" />
                        <span className="text-xs sm:hidden">Grid</span>
                    </ToggleGroupItem>
                    <ToggleGroupItem
                        value="list"
                        aria-label="Tampilan daftar"
                        className="rounded-lg px-3"
                    >
                        <LayoutList className="size-4" />
                        <span className="text-xs sm:hidden">List</span>
                    </ToggleGroupItem>
                </ToggleGroup>
            </div>

            <div className="flex flex-wrap items-center gap-3">
                <div className="flex w-full min-w-0 items-center gap-2 sm:w-auto sm:flex-none">
                    <span className="hidden text-xs font-medium text-muted-foreground sm:inline-block">
                        Kategori:
                    </span>
                    <Select
                        value={filters.category || 'all'}
                        onValueChange={(val) => applyFilters({ category: val })}
                    >
                        <SelectTrigger className="h-10 w-full rounded-lg shadow-xs sm:w-[220px] sm:flex-none">
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

                <div className="flex flex-wrap items-center gap-3 rounded-xl border bg-background/70 px-3 py-2">
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

                    <Separator orientation="vertical" className="h-5" />

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
                </div>
            </div>
        </div>
    );
}
