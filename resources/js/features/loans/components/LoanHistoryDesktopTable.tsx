import { Link } from '@inertiajs/react';
import BookController from '@/actions/App/Http/Controllers/BookController';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { LoanStatusBadge } from '@/features/loans/components/LoanStatusBadge';
import { instantLoadingPageProps } from '@/lib/inertia-loading';
import { cn } from '@/lib/utils';
import type { LoanHistoryRow } from '@/features/loans/types';

interface LoanHistoryDesktopTableProps {
    loans: LoanHistoryRow[];
    selectedLoanItemIds: number[];
    onToggleSelection: (loanItemId: number, checked: boolean) => void;
}

export function LoanHistoryDesktopTable({
    loans,
    selectedLoanItemIds,
    onToggleSelection,
}: LoanHistoryDesktopTableProps) {
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
