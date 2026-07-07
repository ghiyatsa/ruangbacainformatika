import { Link } from '@inertiajs/react';
import { User, GraduationCap } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { getLevelConfig } from '@/features/similarity/types';
import { cn } from '@/lib/utils';
import type { SimilarityItem } from '@/features/similarity/types';

interface ResultCardProps {
    item: SimilarityItem;
    index: number;
    userTitle?: string;
}

export function SimilarityResultCard({ item, index }: ResultCardProps) {
    const isInternship = item.document_type === 'internship_report';
    const detailUrl = isInternship
        ? `/internship-reports/${item.student_id}`
        : `/skripsi/${item.student_id}`;

    const cfg = getLevelConfig(item.level);
    const LevelIcon = cfg.icon;

    const content = (
        <CardContent className="flex flex-col gap-5 p-5 sm:p-6">
            <div className="flex flex-col justify-between gap-5 lg:flex-row">
                <div className="flex min-w-0 flex-col gap-2">
                    <div className="flex items-center gap-2">
                        <span
                            className={cn(
                                'flex size-8 shrink-0 items-center justify-center rounded-md text-xs font-bold',
                                cfg.bg,
                            )}
                        >
                            #{index + 1}
                        </span>
                    </div>

                    <div className="min-w-0 flex-1 space-y-2">
                        <h3 className="text-base leading-7 font-semibold tracking-tight text-foreground transition-colors group-hover:text-primary sm:text-lg">
                            {item.judul}
                        </h3>
                        <div className="mt-2 flex flex-wrap gap-4 text-xs text-muted-foreground sm:text-sm">
                            <div className="flex items-center gap-1.5">
                                <User className="size-3.5 text-muted-foreground/60" />
                                <span>{item.nama_mahasiswa || '—'}</span>
                            </div>
                            {item.student_id ? (
                                <div className="flex items-center gap-1.5">
                                    <GraduationCap className="size-3.5 text-muted-foreground/60" />
                                    <span>{item.student_id}</span>
                                </div>
                            ) : null}
                        </div>
                    </div>
                </div>
            </div>

            <div className="space-y-2">
                <div className="flex items-center justify-between">
                    <span
                        className={cn(
                            'text-lg font-extrabold tracking-tight tabular-nums sm:text-xl',
                            cfg.color,
                        )}
                    >
                        {item.similarity_persen}%
                    </span>
                    <Badge
                        variant="outline"
                        className={cn(
                            'h-5 gap-1 rounded-full border-none px-2 text-[9px] uppercase shadow-none transition-none',
                            cfg.badgeClass,
                        )}
                    >
                        <LevelIcon className="size-2.5" />
                        {cfg.label}
                    </Badge>
                </div>

                <div className="h-1.5 w-full overflow-hidden rounded-full bg-muted dark:bg-muted">
                    <div
                        className={cn(
                            'h-full rounded-full transition-all duration-500 ease-out',
                            cfg.bg,
                        )}
                        style={{ width: `${item.similarity_persen}%` }}
                    />
                </div>
            </div>
        </CardContent>
    );

    return (
        <Card
            className={cn(
                'group overflow-hidden rounded-2xl border bg-background ring-0',
                'py-0',
            )}
        >
            {item.student_id ? (
                <Link
                    href={detailUrl}
                    className="block h-full outline-none focus-visible:ring-1 focus-visible:ring-primary"
                >
                    {content}
                </Link>
            ) : (
                content
            )}
        </Card>
    );
}
