import { Head, Link, router, useForm } from '@inertiajs/react';
import {
    AlertTriangle,
    BookOpen,
    ChevronDown,
    CheckCircle2,
    Clock,
    Download,
    Library,
    QrCode,
    Search,
    X,
} from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import BookController from '@/actions/App/Http/Controllers/BookController';
import ReturnDraftController from '@/actions/App/Http/Controllers/ReturnDraftController';
import { ResourcePagination } from '@/components/catalog/ResourcePagination';
import InputError from '@/components/common/InputError';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { instantLoadingPageProps } from '@/lib/inertia-loading';
import { downloadSvgAsPng } from '@/lib/utils';
import { cn } from '@/lib/utils';
import booksRoute from '@/routes/books';
import loansRoute from '@/routes/loans';
import type { FormEvent } from 'react';
import type { PaginationData } from '@/types/pagination';

interface LoanHistoryRow {
    id: number;
    loanId: number;
    bookTitle: string;
    bookSlug: string;
    internalCode: string;
    borrowedAt: string;
    dueAt: string;
    returnedAt: string;
    status: string;
    statusLabel: string;
    isOverdue: boolean;
    isReturned: boolean;
}

interface Props {
    loans: PaginationData<LoanHistoryRow>;
    filters: {
        filter: LoanFilter;
        search: string;
    };
    stats: {
        total: number;
        active: number;
        overdue: number;
        returned: number;
    };
    returnDraft: {
        id: number | null;
        status: string | null;
        itemsCount: number;
        expiresAt: string | null;
        expiresAtIso: string | null;
        hasActiveQr: boolean;
        qrCodeSvg: string | null;
        selectedLoanItemIds: number[];
        items: Array<{
            loanItemId: number;
            bookTitle: string;
            internalCode: string;
            borrowedAt: string;
            dueAt: string;
        }>;
    };
}

type LoanFilter = 'all' | 'overdue' | 'active' | 'returned';

function formatCountdown(totalSeconds: number): string {
    if (totalSeconds <= 0) {
        return '00:00';
    }

    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    if (hours > 0) {
        return [hours, minutes, seconds]
            .map((value) => String(value).padStart(2, '0'))
            .join(':');
    }

    return [minutes, seconds]
        .map((value) => String(value).padStart(2, '0'))
        .join(':');
}

function LoanStatusBadge({ loan }: { loan: LoanHistoryRow }) {
    if (loan.isReturned) {
        return (
            <div className="flex items-center gap-2">
                <Badge
                    variant="success"
                    className="gap-1.5 border border-emerald-200/60 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/50 dark:text-emerald-400"
                >
                    <CheckCircle2 className="size-3" />
                    {loan.statusLabel}
                </Badge>
                {loan.isOverdue ? (
                    <Badge
                        variant="destructive"
                        className="h-5 px-1.5 text-[10px] font-bold tracking-wider uppercase"
                    >
                        Terlambat
                    </Badge>
                ) : null}
            </div>
        );
    }

    if (loan.isOverdue) {
        return (
            <Badge
                variant="destructive"
                className="gap-1.5 px-2.5 py-1 text-xs font-semibold"
            >
                <AlertTriangle className="size-3" />
                Terlambat
            </Badge>
        );
    }

    return (
        <Badge
            variant="secondary"
            className="gap-1.5 border border-blue-200/60 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:border-blue-800/60 dark:bg-blue-950/50 dark:text-blue-400"
        >
            <Clock className="size-3" />
            {loan.statusLabel}
        </Badge>
    );
}

function LoanHistoryMobileCard({
    loan,
    isSelected,
    onToggleSelection,
}: {
    loan: LoanHistoryRow;
    isSelected: boolean;
    onToggleSelection: (checked: boolean) => void;
}) {
    return (
        <div className="rounded-2xl border border-border/60 bg-card/80 p-4 shadow-none">
            <div className="flex items-start gap-3">
                <div className="pt-0.5">
                    {!loan.isReturned ? (
                        <Checkbox
                            checked={isSelected}
                            onCheckedChange={(checked) =>
                                onToggleSelection(checked === true)
                            }
                            aria-label={`Pilih ${loan.bookTitle}`}
                        />
                    ) : (
                        <div className="flex size-4 items-center justify-center text-xs text-muted-foreground">
                            -
                        </div>
                    )}
                </div>

                <div className="min-w-0 flex-1 space-y-3">
                    <div className="space-y-2">
                        <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div className="min-w-0 space-y-1">
                                <Link
                                    href={BookController.show.url(
                                        loan.bookSlug,
                                    )}
                                    instant
                                    component="books/show"
                                    pageProps={instantLoadingPageProps()}
                                    className="line-clamp-2 font-semibold text-foreground transition-colors hover:text-primary"
                                >
                                    {loan.bookTitle}
                                </Link>
                                <p className="text-xs text-muted-foreground">
                                    Transaksi #{loan.loanId}
                                </p>
                            </div>

                            <div className="shrink-0">
                                <LoanStatusBadge loan={loan} />
                            </div>
                        </div>

                        <div className="rounded-xl bg-muted/30 p-3">
                            <p className="font-mono text-[11px] tracking-wider text-muted-foreground uppercase">
                                Kode Buku
                            </p>
                            <p className="mt-1 text-sm font-medium text-foreground">
                                {loan.internalCode}
                            </p>
                        </div>
                    </div>

                    <dl className="grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
                        <div className="space-y-1 rounded-xl border border-border/50 p-3">
                            <dt className="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">
                                Dipinjam
                            </dt>
                            <dd className="text-foreground">
                                {loan.borrowedAt}
                            </dd>
                        </div>
                        <div className="space-y-1 rounded-xl border border-border/50 p-3">
                            <dt className="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">
                                Jatuh Tempo
                            </dt>
                            <dd
                                className={cn(
                                    'text-foreground',
                                    loan.isOverdue && !loan.isReturned
                                        ? 'font-semibold text-destructive'
                                        : '',
                                )}
                            >
                                {loan.dueAt}
                            </dd>
                        </div>
                        <div className="space-y-1 rounded-xl border border-border/50 p-3">
                            <dt className="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">
                                Dikembalikan
                            </dt>
                            <dd className="text-foreground">
                                {loan.returnedAt}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    );
}

function LoanHistoryDesktopTable({
    loans,
    selectedLoanItemIds,
    onToggleSelection,
}: {
    loans: LoanHistoryRow[];
    selectedLoanItemIds: number[];
    onToggleSelection: (loanItemId: number, checked: boolean) => void;
}) {
    return (
        <Table className="min-w-[760px]">
            <TableHeader>
                <TableRow>
                    <TableHead className="w-14">Pilih</TableHead>
                    <TableHead>Buku</TableHead>
                    <TableHead>Kode</TableHead>
                    <TableHead>Dipinjam</TableHead>
                    <TableHead>Jatuh Tempo</TableHead>
                    <TableHead>Dikembalikan</TableHead>
                    <TableHead>Status</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {loans.map((loan) => (
                    <TableRow key={loan.id}>
                        <TableCell className="align-top">
                            {!loan.isReturned ? (
                                <Checkbox
                                    checked={selectedLoanItemIds.includes(
                                        loan.id,
                                    )}
                                    onCheckedChange={(checked) =>
                                        onToggleSelection(
                                            loan.id,
                                            checked === true,
                                        )
                                    }
                                    aria-label={`Pilih ${loan.bookTitle}`}
                                    className="mt-1"
                                />
                            ) : (
                                <span className="text-xs text-muted-foreground">
                                    -
                                </span>
                            )}
                        </TableCell>
                        <TableCell className="align-top whitespace-normal">
                            <div className="min-w-[240px] space-y-1">
                                <Link
                                    href={BookController.show.url(
                                        loan.bookSlug,
                                    )}
                                    instant
                                    component="books/show"
                                    pageProps={instantLoadingPageProps()}
                                    className="line-clamp-2 font-semibold text-foreground transition-colors hover:text-primary"
                                >
                                    {loan.bookTitle}
                                </Link>
                                <p className="text-xs text-muted-foreground">
                                    Transaksi #{loan.loanId}
                                </p>
                            </div>
                        </TableCell>
                        <TableCell className="font-mono text-xs tracking-wider text-muted-foreground">
                            {loan.internalCode}
                        </TableCell>
                        <TableCell>{loan.borrowedAt}</TableCell>
                        <TableCell
                            className={cn(
                                loan.isOverdue && !loan.isReturned
                                    ? 'font-semibold text-destructive'
                                    : '',
                            )}
                        >
                            {loan.dueAt}
                        </TableCell>
                        <TableCell>{loan.returnedAt}</TableCell>
                        <TableCell>
                            <LoanStatusBadge loan={loan} />
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}

function StatsBar({ stats }: { stats: Props['stats'] }) {
    const { total, active, overdue, returned } = stats;

    return (
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
            {[
                {
                    label: 'Total Buku',
                    value: total,
                    icon: Library,
                    color: 'text-primary',
                    bg: 'bg-primary/8',
                },
                {
                    label: 'Buku Aktif',
                    value: active,
                    icon: BookOpen,
                    color: 'text-blue-600 dark:text-blue-400',
                    bg: 'bg-blue-50 dark:bg-blue-950/40',
                },
                {
                    label: 'Terlambat',
                    value: overdue,
                    icon: AlertTriangle,
                    color: 'text-destructive',
                    bg: 'bg-destructive/8',
                },
                {
                    label: 'Dikembalikan',
                    value: returned,
                    icon: CheckCircle2,
                    color: 'text-emerald-600 dark:text-emerald-400',
                    bg: 'bg-emerald-50 dark:bg-emerald-950/40',
                },
            ].map((stat) => (
                <Card
                    key={stat.label}
                    className="border-border/60 bg-card/80 shadow-none"
                >
                    <CardContent className="flex items-center gap-3 p-4">
                        <div
                            className={cn(
                                'flex size-9 shrink-0 items-center justify-center rounded-lg',
                                stat.bg,
                            )}
                        >
                            <stat.icon className={cn('size-4', stat.color)} />
                        </div>
                        <div className="min-w-0">
                            <p className="truncate text-[11px] font-medium tracking-wider text-muted-foreground uppercase">
                                {stat.label}
                            </p>
                            <p className="text-xl leading-tight font-bold text-foreground">
                                {stat.value}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            ))}
        </div>
    );
}

export default function LoanHistoryPage({
    loans,
    filters,
    stats,
    returnDraft,
}: Props) {
    const [showReturnedLoans, setShowReturnedLoans] = useState(
        () =>
            filters.filter === 'returned' ||
            (stats.active === 0 && stats.overdue === 0),
    );
    const [searchQuery, setSearchQuery] = useState(filters.search);
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
    const qrForm = useForm<{
        loan_item_ids: number[];
    }>({
        loan_item_ids: defaultSelectedLoanItemIds,
    });
    const { clearErrors, setData } = qrForm;
    const [currentTimestamp, setCurrentTimestamp] = useState(() => Date.now());
    const expiresAtTimestamp = returnDraft.expiresAtIso
        ? new Date(returnDraft.expiresAtIso).getTime()
        : null;
    const remainingSeconds =
        expiresAtTimestamp === null
            ? null
            : Math.max(
                  Math.ceil((expiresAtTimestamp - currentTimestamp) / 1000),
                  0,
              );
    const countdownLabel =
        remainingSeconds === null ? null : formatCountdown(remainingSeconds);
    const hasActiveQrCountdown =
        returnDraft.hasActiveQr &&
        remainingSeconds !== null &&
        remainingSeconds > 0;

    useEffect(() => {
        setData('loan_item_ids', defaultSelectedLoanItemIds);
        clearErrors();
    }, [clearErrors, defaultSelectedLoanItemIds, returnDraft.id, setData]);

    useEffect(() => {
        if (expiresAtTimestamp === null) {
            return;
        }

        const interval = window.setInterval(() => {
            setCurrentTimestamp(Date.now());
        }, 1000);

        return () => window.clearInterval(interval);
    }, [expiresAtTimestamp]);

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
    const visibleLoansCount = loans.total;
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
                  onRemove: () => applyHistoryFilters('all', searchQuery),
              }
            : null,
        filters.search !== ''
            ? {
                  key: 'search',
                  label: `"${filters.search}"`,
                  onRemove: () => applyHistoryFilters(filters.filter, ''),
              }
            : null,
    ].filter((chip): chip is NonNullable<typeof chip> => chip !== null);

    const selectedLoanItemIds = qrForm.data.loan_item_ids;
    const selectedItemsCount = selectedLoanItemIds.length;

    const toggleLoanSelection = (loanItemId: number, checked: boolean) => {
        const currentLoanItemIds = qrForm.data.loan_item_ids;

        if (checked) {
            if (currentLoanItemIds.includes(loanItemId)) {
                return;
            }

            qrForm.setData('loan_item_ids', [
                ...currentLoanItemIds,
                loanItemId,
            ]);
        } else {
            qrForm.setData(
                'loan_item_ids',
                currentLoanItemIds.filter(
                    (currentLoanItemId) => currentLoanItemId !== loanItemId,
                ),
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

    const applyHistoryFilters = (
        nextFilter: LoanFilter,
        nextSearch: string,
    ): void => {
        router.get(
            loansRoute.history.url(),
            {
                filter: nextFilter === 'all' ? '' : nextFilter,
                search: nextSearch.trim(),
            },
            {
                preserveScroll: true,
                preserveState: false,
                replace: true,
            },
        );
    };

    useEffect(() => {
        const normalizedSearch = searchQuery.trim();

        if (normalizedSearch === filters.search) {
            return;
        }

        const timeout = window.setTimeout(() => {
            applyHistoryFilters(filters.filter, normalizedSearch);
        }, 300);

        return () => window.clearTimeout(timeout);
    }, [filters.filter, filters.search, searchQuery]);

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

    return (
        <>
            <Head title="Riwayat Peminjaman" />

            <div className="container mx-auto max-w-6xl px-4 py-8 pb-16 sm:px-6 lg:px-8">
                <div className="mb-8">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            Riwayat Peminjaman
                        </h1>
                        <p className="mt-0.5 text-sm text-muted-foreground">
                            Riwayat pinjam ditampilkan per buku agar tetap
                            ringkas saat data bertambah.
                        </p>
                    </div>
                </div>

                {loans.data.length > 0 ? (
                    <div className="grid gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(320px,0.95fr)]">
                        <div className="space-y-6">
                            <StatsBar stats={stats} />

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
                                            {[
                                                {
                                                    key: 'all' as const,
                                                    label: 'Semua',
                                                },
                                                {
                                                    key: 'overdue' as const,
                                                    label: 'Terlambat',
                                                },
                                                {
                                                    key: 'active' as const,
                                                    label: 'Masih dipinjam',
                                                },
                                                {
                                                    key: 'returned' as const,
                                                    label: 'Selesai',
                                                },
                                            ].map((filter) => (
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
                                                        applyHistoryFilters(
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
                                            {' '}
                                            {visibleLoansCount.toLocaleString(
                                                'id-ID',
                                            )}{' '}
                                            hasil
                                        </p>
                                    </div>

                                    {visibleLoansCount === 0 ? (
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
                                                    {
                                                        groupedLoans.overdue
                                                            .length
                                                    }{' '}
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
                                            open={
                                                filters.filter === 'returned'
                                                    ? true
                                                    : showReturnedLoans
                                            }
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
                                                            {(
                                                                filters.filter ===
                                                                'returned'
                                                                    ? true
                                                                    : showReturnedLoans
                                                            )
                                                                ? 'Tutup'
                                                                : 'Buka'}
                                                            <ChevronDown
                                                                className={cn(
                                                                    'size-4 transition-transform',
                                                                    (
                                                                        filters.filter ===
                                                                        'returned'
                                                                            ? true
                                                                            : showReturnedLoans
                                                                    )
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
                                <ResourcePagination
                                    data={loans}
                                    resourceName="riwayat buku"
                                />
                            </div>
                        </div>

                        <Card className="border-border/70 xl:sticky xl:top-24 xl:h-fit">
                            <CardHeader>
                                <CardTitle>QR Pengembalian</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-5">
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase">
                                            Pilihan
                                        </p>
                                        <p className="mt-2 text-sm font-medium text-foreground">
                                            {selectedItemsCount} buku dipilih
                                        </p>
                                    </div>

                                    <Badge variant="secondary">
                                        {stats.active} aktif
                                    </Badge>
                                </div>

                                <InputError
                                    message={
                                        typeof qrForm.errors.loan_item_ids ===
                                        'string'
                                            ? qrForm.errors.loan_item_ids
                                            : undefined
                                    }
                                />
                                <InputError
                                    message={
                                        typeof (
                                            qrForm.errors as Record<
                                                string,
                                                unknown
                                            >
                                        ).draft === 'string'
                                            ? ((
                                                  qrForm.errors as Record<
                                                      string,
                                                      unknown
                                                  >
                                              ).draft as string)
                                            : undefined
                                    }
                                />

                                {returnDraft.items.length > 0 ? (
                                    <div className="rounded-2xl border border-border/60 bg-muted/15 p-4">
                                        <p className="text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase">
                                            Di QR
                                        </p>
                                        <div className="mt-3 space-y-3">
                                            {returnDraft.items.map((item) => (
                                                <div
                                                    key={item.loanItemId}
                                                    className="rounded-xl border border-border/50 bg-background/80 p-3"
                                                >
                                                    <p className="text-sm font-semibold text-foreground">
                                                        {item.bookTitle}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">
                                                        {item.internalCode} ·{' '}
                                                        {item.borrowedAt}
                                                    </p>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                ) : null}

                                <div className="rounded-[1.75rem] border border-border/70 bg-card px-5 py-6 shadow-sm">
                                    {returnDraft.qrCodeSvg && countdownLabel ? (
                                        <div className="mb-5 text-center">
                                            <p className="text-[11px] font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                                Berlaku dalam
                                            </p>
                                            <p
                                                className="mt-2 text-4xl font-semibold tracking-tight text-foreground tabular-nums"
                                                title={
                                                    returnDraft.expiresAt
                                                        ? `Berlaku sampai ${returnDraft.expiresAt}`
                                                        : undefined
                                                }
                                            >
                                                {countdownLabel}
                                            </p>
                                        </div>
                                    ) : null}

                                    {returnDraft.qrCodeSvg ? (
                                        <div className="space-y-4">
                                            <div className="mx-auto w-max rounded-3xl border border-border/50 bg-white p-4 text-primary shadow-sm dark:bg-card dark:text-white">
                                                <div
                                                    className="flex justify-center [&_svg]:mx-auto"
                                                    dangerouslySetInnerHTML={{
                                                        __html: returnDraft.qrCodeSvg,
                                                    }}
                                                />
                                            </div>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                className="mx-auto flex items-center gap-2"
                                                onClick={() =>
                                                    downloadSvgAsPng(
                                                        returnDraft.qrCodeSvg!,
                                                        `qr-pengembalian-${returnDraft.id ?? 'draft'}.png`,
                                                        'QR PENGEMBALIAN',
                                                    )
                                                }
                                            >
                                                <Download className="size-4" />
                                                Unduh QR
                                            </Button>
                                        </div>
                                    ) : (
                                        <div className="rounded-2xl bg-muted/20 px-5 py-12 text-center text-sm text-muted-foreground">
                                            QR belum dibuat.
                                        </div>
                                    )}
                                </div>

                                <form
                                    onSubmit={submitQrRequest}
                                    className="space-y-3"
                                >
                                    {selectedLoanItemIds.map((loanItemId) => (
                                        <input
                                            key={loanItemId}
                                            type="hidden"
                                            name="loan_item_ids[]"
                                            value={loanItemId}
                                        />
                                    ))}

                                    <Button
                                        type="submit"
                                        size="lg"
                                        className="w-full"
                                        disabled={
                                            qrForm.processing ||
                                            stats.active < 1 ||
                                            selectedItemsCount === 0 ||
                                            hasActiveQrCountdown
                                        }
                                    >
                                        <QrCode className="size-4" />
                                        Buat QR
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
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
