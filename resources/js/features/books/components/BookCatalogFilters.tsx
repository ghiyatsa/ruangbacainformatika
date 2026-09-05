import { router } from '@inertiajs/react';
import { LayoutGrid, LayoutList } from 'lucide-react';
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
import { SearchableCatalogFilter } from '@/features/books/components/SearchableCatalogFilter';
import booksRoute from '@/routes/books';
import type {
    CategoryItem,
    AuthorItem,
    PublisherItem,
    ViewMode,
    BookCatalogFilters as FilterTypes,
} from '@/features/books/types';

interface BookCatalogFiltersProps {
    filters: FilterTypes;
    categories: CategoryItem[];
    authors: AuthorItem[];
    publishers: PublisherItem[];
    years: number[];
    viewMode: ViewMode;
    onViewModeChange: (mode: ViewMode) => void;
}

export function BookCatalogFilters({
    filters,
    categories,
    authors,
    publishers,
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
                author: next.author === 'all' ? '' : next.author,
                publisher: next.publisher === 'all' ? '' : next.publisher,
            },
            { preserveScroll: true, replace: true },
        );
    }

    return (
        <div className="flex flex-wrap items-center justify-between gap-3 sm:justify-start">
            <div className="w-[calc(50%-6px)] sm:w-auto sm:flex-none">
                <SearchableCatalogFilter
                    label="Kategori"
                    value={filters.category || ''}
                    placeholder="Pilih Kategori"
                    allLabel="Semua Kategori"
                    searchPlaceholder="Cari kategori..."
                    emptyMessage="Kategori tidak ditemukan."
                    triggerAriaLabel="Filter kategori buku"
                    options={categories}
                    onValueChange={(val) => applyFilters({ category: val })}
                />
            </div>

            <div className="w-[calc(50%-6px)] sm:w-auto sm:flex-none">
                <SearchableCatalogFilter
                    label="Penulis"
                    value={filters.author || ''}
                    placeholder="Pilih Penulis"
                    allLabel="Semua Penulis"
                    searchPlaceholder="Cari penulis..."
                    emptyMessage="Penulis tidak ditemukan."
                    triggerAriaLabel="Filter penulis buku"
                    options={authors}
                    onValueChange={(val) => applyFilters({ author: val })}
                />
            </div>

            <div className="w-[calc(50%-6px)] sm:w-auto sm:flex-none">
                <SearchableCatalogFilter
                    label="Penerbit"
                    value={filters.publisher || ''}
                    placeholder="Pilih Penerbit"
                    allLabel="Semua Penerbit"
                    searchPlaceholder="Cari penerbit..."
                    emptyMessage="Penerbit tidak ditemukan."
                    triggerAriaLabel="Filter penerbit buku"
                    options={publishers}
                    onValueChange={(val) => applyFilters({ publisher: val })}
                />
            </div>

            <div className="flex w-[calc(50%-6px)] items-center gap-2 sm:w-auto sm:flex-none">
                <Select
                    value={filters.year ? String(filters.year) : 'all'}
                    onValueChange={(val) =>
                        applyFilters({
                            year: val === 'all' ? null : Number(val),
                        })
                    }
                >
                    <SelectTrigger
                        aria-label="Filter tahun buku"
                        className="h-10 w-full rounded-lg shadow-xs sm:w-32"
                    >
                        <SelectValue placeholder="Tahun" />
                    </SelectTrigger>
                    <SelectContent position="popper">
                        <SelectItem value="all">Semua</SelectItem>
                        {years.map((y) => (
                            <SelectItem key={y} value={String(y)}>
                                {y}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>

            <div className="flex h-10 w-full items-center justify-center gap-2 rounded-xl border bg-background/70 px-2 py-2 sm:w-auto sm:flex-none sm:justify-start sm:gap-3 sm:px-3">
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
                        className="cursor-pointer text-xs font-medium whitespace-nowrap text-muted-foreground select-none"
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
                        className="cursor-pointer text-xs font-medium whitespace-nowrap text-muted-foreground select-none"
                    >
                        Tersedia
                    </Label>
                </div>
            </div>

            <div className="hidden sm:ml-auto sm:flex">
                <ToggleGroup
                    type="single"
                    value={viewMode}
                    onValueChange={(val) =>
                        val && onViewModeChange(val as ViewMode)
                    }
                    variant="outline"
                    className="gap-1"
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
        </div>
    );
}
