import { Link } from '@inertiajs/react';
import { ExternalLink, User } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { getLevelConfig } from '@/features/similarity/types';
import type { SimilarityItem } from '@/features/similarity/types';

function SimilarityBar({ persen, level }: { persen: number; level: string }) {
    const cfg = getLevelConfig(level);

    return (
        <div className="flex w-full items-center gap-3">
            <div
                className={`h-2 flex-1 overflow-hidden rounded-full ${cfg.trackClass}`}
            >
                <div
                    className={`h-full rounded-full transition-all duration-700 ease-out ${cfg.bg}`}
                    style={{ width: `${persen}%` }}
                />
            </div>
            <span
                className={`w-10 text-right text-sm font-bold tabular-nums ${cfg.color}`}
            >
                {persen}%
            </span>
        </div>
    );
}

interface ResultCardProps {
    item: SimilarityItem;
    index: number;
}

export function SimilarityResultCard({ item, index }: ResultCardProps) {
    const cfg = getLevelConfig(item.level);
    const LevelIcon = cfg.icon;

    const Content = (
        <div className="p-5">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div className="min-w-0 flex-1 space-y-2">
                    <div className="flex items-start gap-3">
                        <span className="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-muted text-[10px] font-bold text-muted-foreground transition-colors group-hover:bg-primary/10 group-hover:text-primary">
                            {index + 1}
                        </span>
                        <div className="space-y-1">
                            <h3 className="text-sm leading-snug font-semibold transition-colors group-hover:text-primary">
                                {item.judul}
                            </h3>
                            <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
                                <User className="size-3 shrink-0" />
                                <span>
                                    {item.nama_mahasiswa || 'Tidak diketahui'}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="flex shrink-0 items-center gap-3 pl-9 sm:pl-0">
                    <Badge
                        variant="outline"
                        className={`gap-1 px-2 py-0.5 text-[10px] font-bold tracking-wider uppercase ${cfg.badgeClass}`}
                    >
                        <LevelIcon className="size-3" />
                        {cfg.label}
                    </Badge>
                    {item.student_id && (
                        <ExternalLink className="size-4 text-muted-foreground/40 transition-colors group-hover:text-primary" />
                    )}
                </div>
            </div>

            <div className="mt-4 pl-9">
                <SimilarityBar
                    persen={item.similarity_persen}
                    level={item.level}
                />
            </div>
        </div>
    );

    return (
        <Card className="group relative overflow-hidden border-muted/60 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg">
            <div
                className={`absolute top-0 left-0 h-full w-1 transition-all duration-300 group-hover:w-1.5 ${cfg.bg}`}
            />

            {item.student_id ? (
                <Link
                    href={`/skripsi/${item.student_id}`}
                    className="block outline-none ring-inset focus-visible:ring-2 focus-visible:ring-primary"
                >
                    {Content}
                </Link>
            ) : (
                Content
            )}
        </Card>
    );
}
