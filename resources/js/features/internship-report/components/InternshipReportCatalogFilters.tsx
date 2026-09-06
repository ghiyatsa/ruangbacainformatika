import { router } from '@inertiajs/react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import internshipReportRoute from '@/routes/internship-reports';
import type { InternshipReportFilters as FilterTypes } from '@/features/internship-report/types';

interface InternshipReportCatalogFiltersProps {
    filters: FilterTypes;
    years: number[];
    total: number;
}

export function InternshipReportCatalogFilters({
    filters,
    years,
}: InternshipReportCatalogFiltersProps) {
    function applyFilters(overrides: Partial<FilterTypes>): void {
        const next = { ...filters, ...overrides };
        router.get(
            internshipReportRoute.index.url(),
            {
                ...(next.year ? { year: String(next.year) } : {}),
                ...(next.search ? { search: next.search } : {}),
            },
            { preserveScroll: true, replace: true },
        );
    }

    return (
        <div className="flex items-center justify-end gap-4">
            <div className="flex flex-1 items-center gap-2 sm:flex-none">
                <Select
                    value={filters.year ? String(filters.year) : 'all'}
                    onValueChange={(val) =>
                        applyFilters({
                            year: val === 'all' ? null : Number(val),
                        })
                    }
                >
                    <SelectTrigger
                        id="internship-report-year-filter"
                        className="h-10 w-full rounded-lg shadow-xs sm:w-36"
                    >
                        <SelectValue placeholder="Semua Tahun" />
                    </SelectTrigger>
                    <SelectContent position="popper">
                        <SelectItem value="all">Semua Tahun</SelectItem>
                        {years.map((y) => (
                            <SelectItem key={y} value={String(y)}>
                                {y}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>
        </div>
    );
}
