import {
    AlertTriangle,
    BookOpen,
    CheckCircle2,
    Library,
} from 'lucide-react';
import {
    Card,
    CardContent,
} from '@/components/ui/card';
import { cn } from '@/lib/utils';

interface StatsBarProps {
    stats: {
        total: number;
        active: number;
        overdue: number;
        returned: number;
    };
}

const statDefinitions = [
    {
        label: 'Total Buku',
        key: 'total' as const,
        icon: Library,
        color: 'text-primary',
        bg: 'bg-primary/8',
    },
    {
        label: 'Buku Aktif',
        key: 'active' as const,
        icon: BookOpen,
        color: 'text-blue-600 dark:text-blue-400',
        bg: 'bg-blue-50 dark:bg-blue-950/40',
    },
    {
        label: 'Terlambat',
        key: 'overdue' as const,
        icon: AlertTriangle,
        color: 'text-destructive',
        bg: 'bg-destructive/8',
    },
    {
        label: 'Dikembalikan',
        key: 'returned' as const,
        icon: CheckCircle2,
        color: 'text-emerald-600 dark:text-emerald-400',
        bg: 'bg-emerald-50 dark:bg-emerald-950/40',
    },
];

export function LoanHistoryStatsBar({ stats }: StatsBarProps) {
    return (
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
            {statDefinitions.map((stat) => (
                <Card
                    key={stat.key}
                    className="border-border/60 bg-card/80 shadow-none"
                >
                    <CardContent className="flex items-center gap-3 p-4">
                        <div
                            className={cn(
                                'flex size-9 shrink-0 items-center justify-center rounded-lg',
                                stat.bg,
                            )}
                        >
                            <stat.icon
                                className={cn('size-4', stat.color)}
                            />
                        </div>
                        <div className="min-w-0">
                            <p className="truncate text-[11px] font-medium tracking-wider text-muted-foreground uppercase">
                                {stat.label}
                            </p>
                            <p className="text-xl leading-tight font-bold text-foreground">
                                {stats[stat.key]}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            ))}
        </div>
    );
}
