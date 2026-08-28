import { Link } from '@inertiajs/react';
import { BookOpen, ChevronDown, Search, X } from 'lucide-react';
import { useMemo, useState } from 'react';
import { PageLayout } from '@/components/layout/PageLayout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import { CatalogPagination } from '@/features/books/components/CatalogPagination';
import { LoanHistoryDesktopTable } from '@/features/loans/components/LoanHistoryDesktopTable';
import { LoanHistoryMobileCard } from '@/features/loans/components/LoanHistoryMobileCard';
import { LoanHistoryStatsBar } from '@/features/loans/components/LoanHistoryStatsBar';
import { useLoanHistoryFilters } from '@/features/loans/hooks/use-loan-history-filters';
import { cn } from '@/lib/utils';
import booksRoute from '@/routes/books';
import type {
    LoanHistoryPageProps,
    LoanHistoryRow,
} from '@/features/loans/types';

const FILTER_OPTIONS = [
    { key: 'all' as const, label: 'Semua' },
    { key: 'overdue' as const, label: 'Terlambat' },
    { key: 'active' as const, label: 'Masih dipinjam' },
    { key: 'returned' as const, label: 'Selesai' },
];

export default function LoanHistoryPage({
    loans,
    filters,
    stats,
}: LoanHistoryPageProps) {
    const [showReturnedLoans, setShowReturnedLoans] = useState(
        () =>
            filters.filter === 'returned' ||
            (stats.active === 0 && stats.overdue === 0),
    );

    const { searchQuery, setSearchQuery, applyFilters } = useLoanHistoryFilters(
        {
            currentFilter: filters.filter,
            currentSearch: filters.search,
        },
    );

    const groupedLoans = useMemo(
        () => ({
            overdue: loans.data.filter(
                (loan) => loan.isOverdue && !loan.isReturned,
            ),
            active: loans.data.filter(
                (loan) => !loan.isOverdue && !loan.isReturned,
            ),
            returned: loans.data.filter((loan) => loan.isReturned),
        }),
        [loans.data],
    );

    const filterCounts = useMemo(
        () => ({
            all: stats.total,
            overdue: stats.overdue,
            active: Math.max(stats.active - stats.overdue, 0),
            returned: stats.returned,
        }),
        [stats.active, stats.overdue, stats.returned, stats.total],
    );

    const activeFilterChips = [
        filters.filter !== 'all'
            ? {
                  key: 'filter',
                  label:
                      filters.filter === 'overdue'
                          ? 'Terlambat'
                          : filters.filter === 'active'
                            ? 'Dipinjam'
                            : 'Selesai',
                  onRemove: () => applyFilters('all', searchQuery),
              }
            : null,
        filters.search !== ''
            ? {
                  key: 'search',
                  label: `"${filters.search}"`,
                  onRemove: () => applyFilters(filters.filter, ''),
              }
            : null,
    ].filter((chip): chip is NonNullable<typeof chip> => chip !== null);

    const renderLoanCollection = (items: LoanHistoryRow[]) => (
        <>
            <div className="space-y-3 md:hidden">
                {items.map((loan) => (
                    <LoanHistoryMobileCard key={loan.id} loan={loan} />
                ))}
            </div>

            <div className="hidden md:block">
                <LoanHistoryDesktopTable loans={items} />
            </div>
        </>
    );

    const hasLoans = stats.total > 0;
    const returnedSectionOpen =
        filters.filter === 'returned' || showReturnedLoans;

    return (
        <PageLayout title="Riwayat Peminjaman">
            <div className="space-y-6">
                <div className="space-y-1">
                    <h1 className="text-2xl font-bold tracking-tight">
                        Riwayat Peminjaman
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Pantau daftar buku yang sedang dipinjam dan riwayat
                        peminjaman Anda sebelumnya.
                    </p>
                </div>

                <LoanHistoryStatsBar stats={stats} />

                {hasLoans ? (
                    <div className="space-y-6">
                        <div className="space-y-4">
                            <div className="space-y-3 border border-border/60 bg-muted/5 p-4">
                                <div className="flex flex-wrap gap-2">
                                    {FILTER_OPTIONS.map((filter) => (
                                        <Button
                                            key={filter.key}
                                            type="button"
                                            variant={
                                                filters.filter === filter.key
                                                    ? 'default'
                                                    : 'outline'
                                            }
                                            size="sm"
                                            className="h-8 rounded-full px-3"
                                            onClick={() =>
                                                applyFilters(
                                                    filter.key,
                                                    searchQuery,
                                                )
                                            }
                                        >
                                            {filter.label}
                                            <span className="text-xs opacity-80">
                                                {filterCounts[filter.key]}
                                            </span>
                                        </Button>
                                    ))}
                                </div>

                                <div className="relative">
                                    <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        value={searchQuery}
                                        onChange={(event) =>
                                            setSearchQuery(event.target.value)
                                        }
                                        placeholder="Cari judul, kode, atau transaksi"
                                        className="pl-9"
                                    />
                                </div>

                                {activeFilterChips.length > 0 ? (
                                    <div className="flex flex-wrap gap-2">
                                        {activeFilterChips.map((chip) => (
                                            <Button
                                                key={chip.key}
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                className="h-8 gap-1 rounded-full px-3 text-xs"
                                                onClick={chip.onRemove}
                                            >
                                                {chip.label}
                                                <X className="size-3.5" />
                                            </Button>
                                        ))}
                                    </div>
                                ) : null}

                                <p className="text-sm text-muted-foreground">
                                    {loans.total.toLocaleString('id-ID')} hasil
                                </p>
                            </div>

                            {loans.total === 0 ? (
                                <div className="border border-dashed border-border/60 bg-muted/5 px-5 py-10 text-center">
                                    <p className="text-sm font-medium text-foreground">
                                        Tidak ada hasil
                                    </p>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        Ubah filter atau kata kunci pencarian.
                                    </p>
                                </div>
                            ) : null}

                            {groupedLoans.overdue.length > 0 ? (
                                <section className="space-y-3">
                                    <div className="flex flex-col gap-2 border border-destructive/20 bg-destructive/5 p-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div className="space-y-1">
                                            <h3 className="text-sm font-semibold text-foreground">
                                                Terlambat
                                            </h3>
                                            <p className="text-sm text-muted-foreground">
                                                Buku ini sudah melewati batas
                                                waktu pengembalian. Silakan
                                                kembalikan di Kiosk Ruang Baca.
                                            </p>
                                        </div>
                                        <Badge
                                            variant="destructive"
                                            className="w-fit"
                                        >
                                            {groupedLoans.overdue.length} buku
                                        </Badge>
                                    </div>

                                    {renderLoanCollection(groupedLoans.overdue)}
                                </section>
                            ) : null}

                            {groupedLoans.active.length > 0 ? (
                                <section className="space-y-3">
                                    <div className="flex flex-col gap-2 border border-blue-200/60 bg-blue-50/60 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-blue-900/60 dark:bg-blue-950/20">
                                        <div className="space-y-1">
                                            <h3 className="text-sm font-semibold text-foreground">
                                                Masih Dipinjam
                                            </h3>
                                            <p className="text-sm text-muted-foreground">
                                                Bawa buku fisik ke Kiosk Mandiri
                                                untuk proses pengembalian.
                                            </p>
                                        </div>
                                        <Badge
                                            variant="secondary"
                                            className="w-fit"
                                        >
                                            {groupedLoans.active.length} buku
                                        </Badge>
                                    </div>

                                    {renderLoanCollection(groupedLoans.active)}
                                </section>
                            ) : null}

                            {groupedLoans.returned.length > 0 ? (
                                <Collapsible
                                    open={returnedSectionOpen}
                                    onOpenChange={setShowReturnedLoans}
                                    className="border border-border/60"
                                >
                                    <div className="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div className="space-y-1">
                                            <h3 className="text-sm font-semibold text-foreground">
                                                Selesai Dikembalikan
                                            </h3>
                                            <p className="text-sm text-muted-foreground">
                                                Daftar peminjaman yang telah
                                                selesai.
                                            </p>
                                        </div>

                                        <div className="flex items-center gap-2">
                                            <Badge
                                                variant="outline"
                                                className="w-fit"
                                            >
                                                {groupedLoans.returned.length}{' '}
                                                buku
                                            </Badge>
                                            <CollapsibleTrigger asChild>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    className="gap-2"
                                                >
                                                    {returnedSectionOpen
                                                        ? 'Tutup'
                                                        : 'Buka'}
                                                    <ChevronDown
                                                        className={cn(
                                                            'size-4 transition-transform',
                                                            returnedSectionOpen
                                                                ? 'rotate-180'
                                                                : '',
                                                        )}
                                                    />
                                                </Button>
                                            </CollapsibleTrigger>
                                        </div>
                                    </div>

                                    <CollapsibleContent className="border-t border-border/60 p-4 pt-4">
                                        {renderLoanCollection(
                                            groupedLoans.returned,
                                        )}
                                    </CollapsibleContent>
                                </Collapsible>
                            ) : null}
                        </div>

                        <div className="pt-2">
                            <CatalogPagination
                                data={loans}
                                resourceName="riwayat buku"
                            />
                        </div>
                    </div>
                ) : (
                    <div className="flex h-72 flex-col items-center justify-center border border-dashed border-border/60 bg-muted/5 p-6 text-center">
                        <h2 className="text-lg font-bold">Belum ada riwayat</h2>
                        <p className="mt-2 max-w-xs text-sm text-muted-foreground">
                            Anda belum pernah meminjam buku. Kunjungi Ruang Baca
                            Informatika untuk meminjam buku secara mandiri di
                            Kiosk.
                        </p>
                        <Button
                            asChild
                            variant="outline"
                            className="mt-6 gap-2"
                        >
                            <Link href={booksRoute.index.url()}>
                                <BookOpen className="size-4" />
                                Buka Katalog Buku
                            </Link>
                        </Button>
                    </div>
                )}
            </div>
        </PageLayout>
    );
}
