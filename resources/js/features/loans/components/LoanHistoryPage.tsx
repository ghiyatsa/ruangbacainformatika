import { Head, Link, useForm } from '@inertiajs/react';
import {
    AlertTriangle,
    BookOpen,
    CheckCircle2,
    Clock,
    History,
    Library,
    QrCode,
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { instantLoadingPageProps } from '@/lib/inertia-loading';
import { cn } from '@/lib/utils';
import booksRoute from '@/routes/books';
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

export default function LoanHistoryPage({ loans, stats, returnDraft }: Props) {
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

    return (
        <>
            <Head title="Riwayat Peminjaman" />

            <div className="container mx-auto max-w-6xl py-8 pb-16">
                <div className="mb-8">
                    <div className="flex items-center gap-3">
                        <div className="flex size-11 items-center justify-center rounded-xl bg-primary/10 ring-1 ring-primary/20">
                            <History className="size-5 text-primary" />
                        </div>
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
                </div>

                {loans.data.length > 0 ? (
                    <div className="grid gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(320px,0.95fr)]">
                        <div className="space-y-6">
                            <StatsBar stats={stats} />

                            <Card className="border-border/60">
                                <CardHeader className="gap-1.5">
                                    <CardTitle>Daftar Peminjaman</CardTitle>
                                    <CardDescription>
                                        Pilih buku yang masih aktif untuk
                                        dibuatkan QR pengembalian.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead className="w-14">
                                                    Pilih
                                                </TableHead>
                                                <TableHead>Buku</TableHead>
                                                <TableHead>Kode</TableHead>
                                                <TableHead>Dipinjam</TableHead>
                                                <TableHead>
                                                    Jatuh Tempo
                                                </TableHead>
                                                <TableHead>
                                                    Dikembalikan
                                                </TableHead>
                                                <TableHead>Status</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {loans.data.map((loan) => (
                                                <TableRow key={loan.id}>
                                                    <TableCell className="align-top">
                                                        {!loan.isReturned ? (
                                                            <input
                                                                type="checkbox"
                                                                checked={selectedLoanItemIds.includes(
                                                                    loan.id,
                                                                )}
                                                                onChange={(
                                                                    event,
                                                                ) =>
                                                                    toggleLoanSelection(
                                                                        loan.id,
                                                                        event
                                                                            .target
                                                                            .checked,
                                                                    )
                                                                }
                                                                aria-label={`Pilih ${loan.bookTitle}`}
                                                                className="mt-1 size-4 rounded border-input text-primary focus:ring-primary"
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
                                                                Transaksi #
                                                                {loan.loanId}
                                                            </p>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell className="font-mono text-xs tracking-wider text-muted-foreground">
                                                        {loan.internalCode}
                                                    </TableCell>
                                                    <TableCell>
                                                        {loan.borrowedAt}
                                                    </TableCell>
                                                    <TableCell
                                                        className={cn(
                                                            loan.isOverdue &&
                                                                !loan.isReturned
                                                                ? 'font-semibold text-destructive'
                                                                : '',
                                                        )}
                                                    >
                                                        {loan.dueAt}
                                                    </TableCell>
                                                    <TableCell>
                                                        {loan.returnedAt}
                                                    </TableCell>
                                                    <TableCell>
                                                        <LoanStatusBadge
                                                            loan={loan}
                                                        />
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
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
                                <CardTitle className="flex items-center gap-2">
                                    <QrCode className="size-5 text-primary" />
                                    QR Pengembalian
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-5">
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase">
                                            Pilihan
                                        </p>
                                        <p className="mt-2 text-sm font-medium text-foreground">
                                            {selectedItemsCount} buku aktif
                                            dipilih
                                        </p>
                                    </div>

                                    <Badge variant="secondary">
                                        Aktif {stats.active}
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
                                            Termuat di QR
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
                                                        {item.internalCode} •
                                                        Dipinjam{' '}
                                                        {item.borrowedAt}
                                                    </p>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                ) : null}

                                <div className="rounded-[1.75rem] border border-border/70 bg-card px-5 py-6 shadow-sm">
                                    {countdownLabel ? (
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
                                    ) : (
                                        <div className="mb-5 text-center">
                                            <p className="text-sm text-muted-foreground">
                                                QR belum aktif
                                            </p>
                                        </div>
                                    )}

                                    {returnDraft.qrCodeSvg ? (
                                        <div className="mx-auto w-max rounded-3xl border border-border/70 bg-white p-4 shadow-sm">
                                            <div
                                                className="flex justify-center [&_svg]:mx-auto"
                                                dangerouslySetInnerHTML={{
                                                    __html: returnDraft.qrCodeSvg,
                                                }}
                                            />
                                        </div>
                                    ) : (
                                        <div className="rounded-2xl border border-dashed border-border/70 bg-muted/20 px-5 py-12 text-center text-sm text-muted-foreground">
                                            QR siap dibuat dari pilihan buku
                                            aktif.
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
                                        Buat QR Pengembalian
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
                    </div>
                ) : (
                    <Card className="flex h-72 flex-col items-center justify-center border-dashed text-center">
                        <CardTitle className="text-lg">
                            Belum ada riwayat peminjaman
                        </CardTitle>
                        <CardDescription className="mt-2 max-w-xs">
                            Anda belum pernah meminjam buku di perpustakaan ini.
                            Kunjungi katalog untuk menemukan buku menarik.
                        </CardDescription>
                        <Button
                            asChild
                            variant="outline"
                            className="mt-6 gap-2"
                        >
                            <Link href={booksRoute.index.url()}>
                                <BookOpen className="size-4" />
                                Jelajahi Katalog
                            </Link>
                        </Button>
                    </Card>
                )}
            </div>
        </>
    );
}
