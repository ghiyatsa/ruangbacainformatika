import { Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { getLevelConfig } from '@/features/similarity/types';
import { cn } from '@/lib/utils';
import type { SimilarityItem } from '@/features/similarity/types';

function SimilarityBar({ persen, level }: { persen: number; level: string }) {
    const cfg = getLevelConfig(level);
    const LevelIcon = cfg.icon;

    return (
        <div className="space-y-1.5">
            <div className="flex items-center justify-between">
                <Badge
                    variant="outline"
                    className={`h-6 rounded-full px-2.5 text-[10px] font-semibold uppercase ${cfg.badgeClass}`}
                >
                    <LevelIcon className="size-3" />
                    {cfg.label}
                </Badge>
                <div className="flex items-center gap-2">
                    <span className="text-[10px] font-semibold tracking-[0.18em] text-muted-foreground uppercase">
                        Kemiripan
                    </span>
                    <span
                        className={`text-sm font-semibold tabular-nums ${cfg.color}`}
                    >
                        {persen}%
                    </span>
                </div>
            </div>
            <div
                className={`h-2 w-full overflow-hidden rounded-full ${cfg.trackClass}`}
            >
                <div
                    className={`h-full rounded-full ${cfg.bg}`}
                    style={{ width: `${persen}%` }}
                />
            </div>
        </div>
    );
}

// Smart keyword highlighter excluding common Indonesian academic stopwords
function highlightMatchingWords(resultTitle: string, userTitle?: string) {
    if (!userTitle || !resultTitle) {
        return (
            <span className="font-semibold text-foreground">{resultTitle}</span>
        );
    }

    const stopwords = new Set([
        'dan',
        'yang',
        'untuk',
        'pada',
        'dengan',
        'dari',
        'ke',
        'di',
        'ini',
        'itu',
        'atau',
        'sebagai',
        'dalam',
        'tentang',
        'oleh',
        'adalah',
        'adapun',
        'serta',
        'pada',
        'sebuah',
        'berbasis',
        'menggunakan',
        'analisis',
        'implementasi',
        'rancang',
        'bangun',
        'sistem',
        'aplikasi',
        'metode',
        'studi',
        'kasus',
        'algoritma',
        'perancangan',
        'pembuatan',
        'penerapan',
    ]);

    // Clean user words to construct comparison seeds
    const cleanUserWords = userTitle
        .toLowerCase()
        .replace(/[^\w\s]/g, '')
        .split(/\s+/)
        .filter((word) => word.length >= 3 && !stopwords.has(word));

    if (cleanUserWords.length === 0) {
        return (
            <span className="font-semibold text-foreground">{resultTitle}</span>
        );
    }

    // Escape regex special chars in words using standard MDN pattern
    const escapedWords = cleanUserWords.map((word) =>
        word.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'),
    );

    // Regex to match exact words or boundaries
    const pattern = new RegExp(`\\b(${escapedWords.join('|')})\\b`, 'gi');
    const parts = resultTitle.split(pattern);

    return (
        <span className="leading-snug font-semibold text-foreground">
            {parts.map((part, index) => {
                const isMatch = cleanUserWords.some(
                    (w) => w === part.toLowerCase(),
                );

                return isMatch ? (
                    <span
                        key={index}
                        className={cn(
                            'rounded-sm bg-primary/10 px-1 py-0.5 text-foreground ring-1 ring-primary/15',
                            'dark:bg-primary/15 dark:ring-primary/20',
                        )}
                    >
                        {part}
                    </span>
                ) : (
                    <span key={index}>{part}</span>
                );
            })}
        </span>
    );
}

interface ResultCardProps {
    item: SimilarityItem;
    index: number;
    userTitle?: string;
}

export function SimilarityResultCard({
    item,
    index,
    userTitle,
}: ResultCardProps) {
    const content = (
        <>
            <CardHeader className="gap-3 pb-4">
                <div className="flex items-start gap-3">
                    <span className="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-muted text-[10px] font-semibold text-muted-foreground">
                        {index + 1}
                    </span>

                    <div className="min-w-0 flex-1 space-y-3 pt-1">
                        <div className="space-y-2">
                            <h3 className="text-sm leading-6 sm:text-[15px]">
                                {highlightMatchingWords(item.judul, userTitle)}
                            </h3>
                            <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
                                <span className="font-medium">
                                    {item.nama_mahasiswa || '—'}
                                    {' | '}
                                    {item.student_id || '—'}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </CardHeader>
            <Separator />
            <CardContent className="pt-4">
                <SimilarityBar
                    persen={item.similarity_persen}
                    level={item.level}
                />
            </CardContent>
        </>
    );

    return (
        <Card className="overflow-hidden border-border/60 bg-card/95 shadow-sm transition-colors hover:bg-card">
            {item.student_id ? (
                <Link
                    href={`/skripsi/${item.student_id}`}
                    className="block outline-none ring-inset focus-visible:ring-2 focus-visible:ring-primary"
                >
                    {content}
                </Link>
            ) : (
                content
            )}
        </Card>
    );
}
