import { Link } from '@inertiajs/react';
import BookController from '@/actions/App/Http/Controllers/BookController';
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
import type { LoanHistoryRow } from '@/features/loans/types';

interface LoanHistoryDesktopTableProps {
    loans: LoanHistoryRow[];
}

export function LoanHistoryDesktopTable({
    loans,
}: LoanHistoryDesktopTableProps) {
    return (
        <Table className="min-w-[760px]">
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
                {loans.map((loan) => (
                    <TableRow key={loan.id}>
                        <TableCell className="max-w-[320px] font-medium">
                            <Link
                                href={BookController.show.url(loan.bookSlug)}
                                instant
                                component="books/show"
                                pageProps={instantLoadingPageProps()}
                                className="line-clamp-2 transition-colors hover:text-primary"
                            >
                                {loan.bookTitle}
                            </Link>
                        </TableCell>
                        <TableCell className="font-mono text-xs text-muted-foreground">
                            {loan.internalCode}
                        </TableCell>
                        <TableCell className="text-xs text-muted-foreground">
                            {loan.borrowedAt}
                        </TableCell>
                        <TableCell
                            className={`text-xs ${
                                loan.isOverdue && !loan.isReturned
                                    ? 'font-medium text-destructive'
                                    : 'text-muted-foreground'
                            }`}
                        >
                            {loan.dueAt}
                        </TableCell>
                        <TableCell className="text-xs text-muted-foreground">
                            {loan.isReturned ? loan.returnedAt : '-'}
                        </TableCell>
                        <TableCell>
                            <LoanStatusBadge loan={loan} />
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
