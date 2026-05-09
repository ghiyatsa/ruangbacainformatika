import { Head, Link } from '@inertiajs/react';
import {
    AlertTriangle,
    ArrowRight,
    BookOpen,
    Calendar,
    CheckCircle2,
    Clock,
    History,
    Library,
    RotateCcw,
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
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import booksRoute from '@/routes/books';
import type { PaginationData } from '@/types/pagination';

interface LoanItem {
    id: number;
    bookTitle: string;
    bookSlug: string;
    internalCode: string;
    returnedAt: string;
    isReturned: boolean;
}

interface Loan {
    id: number;
    status: string;
    statusLabel: string;
    borrowedAt: string;
    dueAt: string;
    returnedAt: string;
    isOverdue: boolean;
    items: LoanItem[];
    itemsCount: number;
}

interface Props {
    loans: PaginationData<Loan>;
    stats: {
        total: number;
        active: number;
        overdue: number;
        returned: number;
    };
}

function LoanStatusBadge({ loan }: { loan: Loan }) {
    if (loan.status === 'returned') {
        return (
            <div className="flex items-center gap-2">
                <Badge
                    variant="success"
                    className="gap-1.5 border border-emerald-200/60 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/50 dark:text-emerald-400"
                >
                    <CheckCircle2 className="size-3" />
                    {loan.statusLabel}
                </Badge>
                {loan.isOverdue && (
                    <Badge
                        variant="destructive"
                        className="h-5 px-1.5 text-[10px] font-bold tracking-wider uppercase"
                    >
                        Terlambat
                    </Badge>
                )}
            </div>
        );
    }

    if (loan.isOverdue) {
        return (
            <Badge
                variant="destructive"
                className="animate-pulse gap-1.5 px-2.5 py-1 text-xs font-semibold"
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

function LoanCard({ loan }: { loan: Loan }) {
    const isReturned = loan.status === 'returned';
    const isOverdue = loan.isOverdue;

    return (
        <Card
            className={cn(
                'overflow-hidden border transition-shadow duration-200 hover:shadow-md',
                isOverdue && !isReturned
                    ? 'border-destructive/30 bg-destructive/2 ring-1 ring-destructive/15'
                    : 'border-border/60',
            )}
        >
            {/* Card Header */}
            <CardHeader className="bg-muted/20 px-6 py-4">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    {/* Date Info */}
                    <div className="flex flex-wrap items-center gap-x-5 gap-y-2">
                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                            <div className="flex size-7 shrink-0 items-center justify-center rounded-md bg-primary/10">
                                <Calendar className="size-3.5 text-primary" />
                            </div>
                            <div className="flex flex-col leading-tight">
                                <span className="text-[10px] font-medium tracking-wider text-muted-foreground/60 uppercase">
                                    Dipinjam
                                </span>
                                <span className="font-medium text-foreground">
                                    {loan.borrowedAt}
                                </span>
                            </div>
                        </div>

                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                            <div
                                className={cn(
                                    'flex size-7 shrink-0 items-center justify-center rounded-md',
                                    isOverdue && !isReturned
                                        ? 'bg-destructive/10'
                                        : 'bg-amber-500/10',
                                )}
                            >
                                <Clock
                                    className={cn(
                                        'size-3.5',
                                        isOverdue && !isReturned
                                            ? 'text-destructive'
                                            : 'text-amber-600',
                                    )}
                                />
                            </div>
                            <div className="flex flex-col leading-tight">
                                <span className="text-[10px] font-medium tracking-wider text-muted-foreground/60 uppercase">
                                    Batas Kembali
                                </span>
                                <span
                                    className={cn(
                                        'font-medium',
                                        isOverdue && !isReturned
                                            ? 'text-destructive'
                                            : 'text-foreground',
                                    )}
                                >
                                    {loan.dueAt}
                                </span>
                            </div>
                        </div>

                        {isReturned && loan.returnedAt !== '-' && (
                            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                <div className="flex size-7 shrink-0 items-center justify-center rounded-md bg-emerald-500/10">
                                    <RotateCcw className="size-3.5 text-emerald-600" />
                                </div>
                                <div className="flex flex-col leading-tight">
                                    <span className="text-[10px] font-medium tracking-wider text-muted-foreground/60 uppercase">
                                        Dikembalikan
                                    </span>
                                    <span className="font-medium text-foreground">
                                        {loan.returnedAt}
                                    </span>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Status Badge */}
                    <div className="shrink-0">
                        <LoanStatusBadge loan={loan} />
                    </div>
                </div>
            </CardHeader>

            {/* Book Items */}
            <CardContent className="p-0">
                <div className="divide-y divide-border/50">
                    {loan.items.map((item, index) => (
                        <div
                            key={item.id}
                            className="group flex items-center gap-4 px-6 py-4 transition-colors hover:bg-muted/20"
                        >
                            {/* Index + Book Icon */}
                            <div className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-muted/60 text-xs font-semibold text-muted-foreground transition-colors group-hover:bg-primary/10 group-hover:text-primary">
                                {index + 1}
                            </div>

                            {/* Book Info */}
                            <div className="min-w-0 flex-1">
                                <Link
                                    href={BookController.show.url(
                                        item.bookSlug,
                                    )}
                                    className="inline-flex items-center gap-1.5 font-semibold text-foreground transition-colors hover:text-primary"
                                >
                                    <span className="line-clamp-1">
                                        {item.bookTitle}
                                    </span>
                                    <ArrowRight className="size-3.5 shrink-0 opacity-0 transition-opacity group-hover:opacity-100" />
                                </Link>
                                <p className="mt-0.5 font-mono text-[11px] tracking-wider text-muted-foreground">
                                    {item.internalCode}
                                </p>
                            </div>

                            {/* Item Return Status */}
                            <div className="shrink-0">
                                {item.isReturned ? (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200/80 dark:bg-emerald-950/50 dark:text-emerald-400 dark:ring-emerald-800/60">
                                        <CheckCircle2 className="size-3" />
                                        Kembali
                                    </span>
                                ) : (
                                    <span
                                        className={cn(
                                            'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1',
                                            loan.isOverdue
                                                ? 'bg-destructive/10 text-destructive ring-destructive/20'
                                                : 'bg-blue-50 text-blue-700 ring-blue-200/80 dark:bg-blue-950/50 dark:text-blue-400 dark:ring-blue-800/60',
                                        )}
                                    >
                                        {loan.isOverdue ? (
                                            <AlertTriangle className="size-3" />
                                        ) : (
                                            <BookOpen className="size-3" />
                                        )}
                                        {loan.isOverdue
                                            ? 'Terlambat'
                                            : 'Dipinjam'}
                                    </span>
                                )}
                            </div>
                        </div>
                    ))}
                </div>

                {loan.itemsCount > 1 && (
                    <div className="border-t border-border/40 bg-muted/10 px-6 py-2.5">
                        <p className="text-xs text-muted-foreground">
                            Total{' '}
                            <span className="font-semibold text-foreground">
                                {loan.itemsCount}
                            </span>{' '}
                            buku dalam peminjaman ini
                        </p>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

function StatsBar({ stats }: { stats: Props['stats'] }) {
    const { total, active, overdue, returned } = stats;

    return (
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
            {[
                {
                    label: 'Total Peminjaman',
                    value: total,
                    icon: Library,
                    color: 'text-primary',
                    bg: 'bg-primary/8',
                },
                {
                    label: 'Aktif',
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

            <div className="container mx-auto max-w-4xl py-8 pb-16">
                {/* Page Header */}
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
                                Daftar buku yang pernah dan sedang Anda pinjam
                            </p>
                        </div>
                    </div>
                </div>

                {loans.data.length > 0 ? (
                    <div className="space-y-6">
                        {/* Stats */}
                        <StatsBar stats={stats} />

                        <Separator className="opacity-50" />

                        {/* Loan Cards */}
                        <div className="space-y-4">
                            {loans.data.map((loan) => (
                                <LoanCard key={loan.id} loan={loan} />
                            ))}
                        </div>

                        {/* Pagination */}
                        <div className="pt-4">
                            <ResourcePagination
                                data={loans}
                                resourceName="peminjaman"
                            />
                        </div>
                    </div>
                ) : (
                    /* Empty State */
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
