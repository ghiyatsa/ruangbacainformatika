import { Link } from '@inertiajs/react';
import BookController from '@/actions/App/Http/Controllers/BookController';
import { Checkbox } from '@/components/ui/checkbox';
import { LoanStatusBadge } from '@/features/loans/components/LoanStatusBadge';
import { instantLoadingPageProps } from '@/lib/inertia-loading';
import { cn } from '@/lib/utils';
import type { LoanHistoryRow } from '@/features/loans/types';

interface LoanHistoryMobileCardProps {
    loan: LoanHistoryRow;
    isSelected: boolean;
    onToggleSelection: (checked: boolean) => void;
}

export function LoanHistoryMobileCard({
    loan,
    isSelected,
    onToggleSelection,
}: LoanHistoryMobileCardProps) {
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
                                    href={BookController.show.url(loan.bookSlug)}
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
