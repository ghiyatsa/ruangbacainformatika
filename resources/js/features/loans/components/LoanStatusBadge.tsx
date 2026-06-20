import {
    AlertTriangle,
    CheckCircle2,
    Clock,
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import type { LoanHistoryRow } from '@/features/loans/types';

export function LoanStatusBadge({ loan }: { loan: LoanHistoryRow }) {
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
