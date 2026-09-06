import { Link } from '@inertiajs/react';
import BookController from '@/actions/App/Http/Controllers/BookController';
import { LoanStatusBadge } from '@/features/loans/components/LoanStatusBadge';
import { instantLoadingPageProps } from '@/lib/inertia-loading';
import type { LoanHistoryRow } from '@/features/loans/types';

interface LoanHistoryMobileCardProps {
    loan: LoanHistoryRow;
}

export function LoanHistoryMobileCard({ loan }: LoanHistoryMobileCardProps) {
    return (
        <div className="rounded-2xl border border-border/60 bg-card/80 p-4 shadow-none">
            <div className="space-y-3">
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
                                Kode: {loan.internalCode}
                            </p>
                        </div>

                        <LoanStatusBadge loan={loan} />
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-2 border-t border-border/60 pt-3 text-xs text-muted-foreground">
                    <div>
                        <span className="block font-medium text-foreground">
                            Dipinjam
                        </span>
                        <span>{loan.borrowedAt}</span>
                    </div>

                    <div>
                        <span className="block font-medium text-foreground">
                            {loan.isReturned ? 'Dikembalikan' : 'Jatuh Tempo'}
                        </span>
                        <span
                            className={
                                loan.isOverdue && !loan.isReturned
                                    ? 'font-medium text-destructive'
                                    : undefined
                            }
                        >
                            {loan.isReturned ? loan.returnedAt : loan.dueAt}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    );
}
