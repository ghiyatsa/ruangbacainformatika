import { Head, Link, useForm } from '@inertiajs/react';
import { BookOpen, ChevronDown, Search, X } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import ReturnDraftController from '@/actions/App/Http/Controllers/ReturnDraftController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import { ReturnDraftPanel } from '@/features/loans/components/ReturnDraftPanel';
import { useLoanHistoryFilters } from '@/features/loans/hooks/use-loan-history-filters';
import { useCountdown } from '@/hooks/use-countdown';
import { cn } from '@/lib/utils';
import booksRoute from '@/routes/books';
import type { FormEvent } from 'react';
import type { LoanHistoryPageProps, LoanHistoryRow } from '@/features/loans/types';

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
    returnDraft,
}: LoanHistoryPageProps) {
    const [showReturnedLoans, setShowReturnedLoans] = useState(
        () =>
            filters.filter === 'returned' ||
            (stats.active === 0 && stats.overdue === 0),
    );

    const { searchQuery, setSearchQuery, applyFilters } =
        useLoanHistoryFilters({
            currentFilter: filters.filter,
            currentSearch: filters.search,
        });

    // Selection state ---------------------------------------------------------

    const activeLoanItemIds = useMemo(
        () =>
            loans.data
                .filter((loan) => !loan.isReturned)
                .map((loan) => loan.id),
        [loans.data],
    );

    const defaultSelectedLoanItemIds = useMemo(() => {
        if (returnDraft.selectedLoanItemIds.length > 0) {
            return returnDraft.selectedLoanItemIds;
        }

        return activeLoanItemIds;
    }, [activeLoanItemIds, returnDraft.selectedLoanItemIds]);

    const qrForm = useForm<{ loan_item_ids: number[] }>({
        loan_item_ids: defaultSelectedLoanItemIds,
    });

    const { clearErrors, setData } = qrForm;

    // Sync default selection when the draft changes server-side.
    useEffect(() => {
        setData('loan_item_ids', defaultSelectedLoanItemIds);
        clearErrors();
    }, [clearErrors, defaultSelectedLoanItemIds, returnDraft.id, setData]);

    // QR countdown ------------------------------------------------------------

    const { countdownLabel, remainingSeconds } = useCountdown(
        returnDraft.expiresAtIso,
    );

    const hasActiveQrCountdown =
        returnDraft.hasActiveQr &&
        remainingSeconds !== null &&
        remainingSeconds > 0;

    // Loan grouping -----------------------------------------------------------

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

    // The server counts "active" inclusively (overdue items are both active
    // and overdue), so we subtract the overlap for the filter chip count.
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

    const selectedLoanItemIds = qrForm.data.loan_item_ids;

    const toggleLoanSelection = (loanItemId: number, checked: boolean) => {
        const current = qrForm.data.loan_item_ids;

        if (checked) {
            if (current.includes(loanItemId)) {
                return;
            }

            qrForm.setData('loan_item_ids', [...current, loanItemId]);
        } else {
            qrForm.setData(
                'loan_item_ids',
                current.filter((id) => id !== loanItemId),
            );
        }

        qrForm.clearErrors('loan_item_ids');
    };

    const submitQrRequest = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        qrForm.post(ReturnDraftController.generateQr.url(), {
            preserveScroll: true,
        });
    };

    // Reusable rendered collection (mobile + desktop) --------------------------

    const renderLoanCollection = (items: LoanHistoryRow[]) => (
        <>
            <div className="space-y-3 md:hidden">
                {items.map((loan) => (
                    <LoanHistoryMobileCard
                        key={loan.id}
                        loan={loan}
                        isSelected={selectedLoanItemIds.includes(loan.id)}
                        onToggleSelection={(checked) =>
                            toggleLoanSelection(loan.id, checked)
                        }
                    />
                ))}
            </div>

            <div className="hidden md:block">
                <LoanHistoryDesktopTable
                    loans={items}
                    selectedLoanItemIds={selectedLoanItemIds}
                    onToggleSelection={toggleLoanSelection}
                />
            </div>
        </>
    );

    // Collapsible "returned" section — deduplicated open expression.

    const returnedSectionOpen =
        filters.filter === 'returned' || showReturnedLoans;

    // ------------------------------------------------------------------------

    return (
        <>
            <Head title="Riwayat Peminjaman" />

            <div className="container mx-auto max-w-6xl px-4 py-8 pb-16 sm:px-6 lg:px-8">
                <div className="mb-8">
                    <h1 className="text-2xl font-bold tracking-tight">
                        Riwayat Peminjaman
                    </h1>
                    <p className="mt-0.5 text-sm text-muted-foreground">
                        Riwayat pinjam ditampilkan per buku agar tetap ringkas
                        saat data bertambah.
                    </p>
                </div>

                {loans.data.length > 0 ? (
                    <div className="grid gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(320px,0.95fr)]">
                        <div className="space-y-6">
                            <LoanHistoryStatsBar stats={stats} />

                            <Card className="border-border/60">
                                <CardHeader className="gap-1.5">
                                    <CardTitle>Daftar Peminjaman</CardTitle>
                                    <CardDescription>
                                        Yang aktif tampil lebih dulu.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-3 rounded-2xl border border-border/60 bg-muted/10 p-4">
                                        <div className="flex flex-wrap gap-2">
                                            {FILTER_OPTIONS.map((filter) => (
                                                <Button
                                                    key={filter.key}
                                                    type="button"
                                                    variant={
                                                        filters.filter ===
                                                        filter.key
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
                                                        {
                                                            filterCounts[
                                                                filter.key
                                                            ]
                                                        }
                                                    </span>
                                                </Button>
                                            ))}
                                        </div>

                                        <div className="relative">
                                            <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                                            <Input
                                                value={searchQuery}
                                                onChange={(event) =>
                                                    setSearchQuery(
                                                        event.target.value,
                                                    )
                                                }
                                                placeholder="Cari judul, kode, atau transaksi"
                                                className="pl-9"
                                            />
                                        </div>

                                        {activeFilterChips.length > 0 ? (
                                            <div className="flex flex-wrap gap-2">
                                                {activeFilterChips.map(
                                                    (chip) => (
                                                        <Button
                                                            key={chip.key}
                                                            type="button"
                                                            variant="outline"
                                                            size="sm"
                                                            className="h-8 gap-1 rounded-full px-3 text-xs"
                                                            onClick={
                                                                chip.onRemove
                                                            }
                                                        >
                                                            {chip.label}
                                                            <X className="size-3.5" />
                                                        </Button>
                                                    ),
                                                )}
                                            </div>
                                        ) : null}

                                        <p className="text-sm text-muted-foreground">
                                            {loans.total.toLocaleString(
                                                'id-ID',
                                            )}{' '}
                                            hasil
                                        </p>
                                    </div>

                                    {loans.total === 0 ? (
                                        <div className="rounded-2xl border border-dashed border-border/70 bg-muted/10 px-5 py-10 text-center">
                                            <p className="text-sm font-medium text-foreground">
                                                Tidak ada hasil
                                            </p>
                                            <p className="mt-1 text-sm text-muted-foreground">
                                                Ubah filter atau kata kunci.
                                            </p>
                                        </div>
                                    ) : null}

                                    {groupedLoans.overdue.length > 0 ? (
                                        <section className="space-y-3">
                                            <div className="flex flex-col gap-2 rounded-2xl border border-destructive/20 bg-destructive/5 p-4 sm:flex-row sm:items-center sm:justify-between">
                                                <div className="space-y-1">
                                                    <h3 className="text-sm font-semibold text-foreground">
                                                        Terlambat
                                                    </h3>
                                                    <p className="text-sm text-muted-foreground">
                                                        Perlu segera
                                                        dikembalikan.
                                                    </p>
                                                </div>
                                                <Badge
                                                    variant="destructive"
                                                    className="w-fit"
                                                >
                                                    {groupedLoans.overdue.length}{' '}
                                                    buku
                                                </Badge>
                                            </div>

                                            {renderLoanCollection(
                                                groupedLoans.overdue,
                                            )}
                                        </section>
                                    ) : null}

                                    {groupedLoans.active.length > 0 ? (
                                        <section className="space-y-3">
                                            <div className="flex flex-col gap-2 rounded-2xl border border-blue-200/60 bg-blue-50/60 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-blue-900/60 dark:bg-blue-950/20">
                                                <div className="space-y-1">
                                                    <h3 className="text-sm font-semibold text-foreground">
                                                        Masih dipinjam
                                                    </h3>
                                                    <p className="text-sm text-muted-foreground">
                                                        Pilih buku untuk QR
                                                        pengembalian.
                                                    </p>
                                                </div>
                                                <Badge
                                                    variant="secondary"
                                                    className="w-fit"
                                                >
                                                    {groupedLoans.active.length}{' '}
                                                    buku
                                                </Badge>
                                            </div>

                                            {renderLoanCollection(
                                                groupedLoans.active,
                                            )}
                                        </section>
                                    ) : null}

                                    {groupedLoans.returned.length > 0 ? (
                                        <Collapsible
                                            open={returnedSectionOpen}
                                            onOpenChange={setShowReturnedLoans}
                                            className="rounded-2xl border border-border/60"
                                        >
                                            <div className="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                                                <div className="space-y-1">
                                                    <h3 className="text-sm font-semibold text-foreground">
                                                        Selesai
                                                    </h3>
                                                    <p className="text-sm text-muted-foreground">
                                                        Riwayat yang sudah
                                                        selesai.
                                                    </p>
                                                </div>

                                                <div className="flex items-center gap-2">
                                                    <Badge
                                                        variant="outline"
                                                        className="w-fit"
                                                    >
                                                        {
                                                            groupedLoans
                                                                .returned.length
                                                        }{' '}
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
                                </CardContent>
                            </Card>

                            <div className="pt-2">
                                <CatalogPagination
                                    data={loans}
                                    resourceName="riwayat buku"
                                />
                            </div>
                        </div>

                        <ReturnDraftPanel
                            returnDraft={returnDraft}
                            activeLoanCount={stats.active}
                            selectedItemsCount={selectedLoanItemIds.length}
                            countdownLabel={countdownLabel}
                            hasActiveQrCountdown={hasActiveQrCountdown}
                            isProcessing={qrForm.processing}
                            errors={qrForm.errors}
                            onSubmit={submitQrRequest}
                        />
                    </div>
                ) : (
                    <Card className="flex h-72 flex-col items-center justify-center border-dashed text-center">
                        <CardTitle className="text-lg">
                            Belum ada riwayat
                        </CardTitle>
                        <CardDescription className="mt-2 max-w-xs">
                            Anda belum pernah meminjam buku.
                        </CardDescription>
                        <Button
                            asChild
                            variant="outline"
                            className="mt-6 gap-2"
                        >
                            <Link href={booksRoute.index.url()}>
                                <BookOpen className="size-4" />
                                Buka Katalog
                            </Link>
                        </Button>
                    </Card>
                )}
            </div>
        </>
    );
}
