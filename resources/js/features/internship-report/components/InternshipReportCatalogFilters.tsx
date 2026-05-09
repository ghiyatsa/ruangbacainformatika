import { router } from '@inertiajs/react';
import { ClipboardCheck } from 'lucide-react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import type { InternshipReportFilters as FilterTypes } from '@/features/internship-report/types';
import internshipReportRoute from '@/routes/internship-reports';

interface InternshipReportCatalogFiltersProps {
    filters: FilterTypes;
    years: number[];
    total: number;
}

export function InternshipReportCatalogFilters({
    filters,
    years,
    total,
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
        <div className="flex items-center justify-between gap-4">
            <div className="flex flex-wrap items-center gap-3">
                {/* Result Count Badge */}
                <div className="hidden items-center gap-2 rounded-lg border bg-muted/30 px-3 py-2 text-xs font-medium text-muted-foreground lg:flex">
                    <ClipboardCheck className="size-3.5" />
                    <span>
                        <strong className="text-foreground">
                            {total.toLocaleString('id-ID')}
                        </strong>{' '}
                        Hasil
                    </span>
                </div>

                <Separator
                    orientation="vertical"
                    className="hidden h-8 lg:block"
                />

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
                        <SelectTrigger
                            id="internship-report-year-filter"
                            className="h-10 w-36 rounded-lg shadow-xs"
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
                </div>
            </div>
        </div>
    );
}
