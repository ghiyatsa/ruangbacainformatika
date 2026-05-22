import { Head, Link } from '@inertiajs/react';
import {
    AlertTriangle,
    BookOpen,
    CheckCircle2,
    Clock,
    History,
    Library,
} from 'lucide-react';
import BookController from '@/actions/App/Http/Controllers/BookController';
import { ResourcePagination } from '@/components/catalog/ResourcePagination';
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
import { cn } from '@/lib/utils';
import booksRoute from '@/routes/books';
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

export default function LoanHistoryPage({ loans, stats }: Props) {
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
                    <div className="space-y-6">
                        <StatsBar stats={stats} />

                        <Card className="border-border/60">
                            <CardHeader className="gap-1.5">
                                <CardTitle>Daftar Peminjaman</CardTitle>
                                <CardDescription>
                                    Status aktif dihitung berdasarkan jumlah
                                    buku yang belum dikembalikan.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Buku</TableHead>
                                            <TableHead>Kode</TableHead>
                                            <TableHead>Dipinjam</TableHead>
                                            <TableHead>Jatuh Tempo</TableHead>
                                            <TableHead>Dikembalikan</TableHead>
                                            <TableHead>Status</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {loans.data.map((loan) => (
                                            <TableRow key={loan.id}>
                                                <TableCell className="align-top whitespace-normal">
                                                    <div className="min-w-[240px] space-y-1">
                                                        <Link
                                                            href={BookController.show.url(
                                                                loan.bookSlug,
                                                            )}
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
