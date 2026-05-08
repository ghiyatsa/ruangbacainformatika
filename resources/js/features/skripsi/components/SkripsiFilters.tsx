import { router } from '@inertiajs/react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { SkripsiFilters as FilterTypes } from '@/features/skripsi/types';
import skripsiRoute from '@/routes/skripsi';

interface SkripsiFiltersProps {
    filters: FilterTypes;
    years: number[];
    total: number;
}

export function SkripsiFilters({ filters, years, total }: SkripsiFiltersProps) {
    const hasActiveFilters = filters.year !== null || filters.search !== '';

    function applyFilters(overrides: Partial<FilterTypes>): void {
        const next = { ...filters, ...overrides };
        router.get(
            skripsiRoute.index.url(),
            {
                ...(next.year ? { year: String(next.year) } : {}),
                ...(next.search ? { search: next.search } : {}),
            },
            { preserveScroll: true, replace: true },
        );
    }

    return (
        <div className="mb-8 space-y-4">
            <div className="flex flex-wrap items-center justify-between gap-4">
                <div className="flex items-center gap-4">
                    <Select
                        value={filters.year ? String(filters.year) : 'all'}
                        onValueChange={(val) =>
                            applyFilters({
                                year: val === 'all' ? null : Number(val),
                            })
                        }
                    >
                        <SelectTrigger
                            id="skripsi-year-filter"
                            className="w-36"
                        >
                            <SelectValue placeholder="Semua Tahun" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua Tahun</SelectItem>
                            {years.map((y) => (
                                <SelectItem key={y} value={String(y)}>
                                    {y}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    {hasActiveFilters && (
                        <p className="text-sm text-muted-foreground">
                            <span className="font-semibold text-foreground">
                                {total.toLocaleString('id-ID')}
                            </span>{' '}
                            hasil ditemukan
                        </p>
                    )}
                </div>
            </div>
        </div>
    );
}
