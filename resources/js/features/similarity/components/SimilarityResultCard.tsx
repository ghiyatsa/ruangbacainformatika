import { Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { getLevelConfig } from '@/features/similarity/types';
import { cn } from '@/lib/utils';
import type { SimilarityItem } from '@/features/similarity/types';

function SimilarityBar({ persen, level }: { persen: number; level: string }) {
    const cfg = getLevelConfig(level);
    const LevelIcon = cfg.icon;

    return (
        <div className="flex flex-col gap-2">
            <div className="flex items-center justify-between">
                <Badge
                    variant="outline"
                    className={cn(
                        'h-5 rounded-full px-2 text-[9px] font-bold uppercase transition-none shadow-none',
                        cfg.badgeClass
                    )}
                >
                    <LevelIcon className="size-2.5 mr-1" />
                    {cfg.label}
                </Badge>
                <div className="flex items-center gap-1.5">
                    <span className="text-[9px] font-bold tracking-widest text-muted-foreground/80 uppercase">
                        Kecocokan
                    </span>
                    <span className={cn('text-xs font-bold tabular-nums', cfg.color)}>
                        {persen}%
                    </span>
                </div>
            </div>
            <div className="h-1.5 w-full bg-muted rounded-none overflow-hidden">
                <div
                    className={cn('h-full', cfg.bg)}
                    style={{ width: `${persen}%` }}
                />
            </div>
        </div>
    );
}

// Smart keyword highlighter excluding common Indonesian academic stopwords
function highlightMatchingWords(resultTitle: string, userTitle?: string) {
    if (!userTitle || !resultTitle) {
        return <span className="font-medium text-foreground">{resultTitle}</span>;
    }

    const stopwords = new Set([
        'dan', 'yang', 'untuk', 'pada', 'dengan', 'dari', 'ke', 'di', 'ini', 'itu', 'atau',
        'sebagai', 'dalam', 'tentang', 'oleh', 'adalah', 'adapun', 'serta', 'sebuah',
        'berbasis', 'menggunakan', 'analisis', 'implementasi', 'rancang', 'bangun',
        'sistem', 'aplikasi', 'metode', 'studi', 'kasus', 'algoritma', 'perancangan',
        'pembuatan', 'penerapan',
    ]);

    const cleanUserWords = userTitle
        .toLowerCase()
        .replace(/[^\w\s]/g, '')
        .split(/\s+/)
        .filter((word) => word.length >= 3 && !stopwords.has(word));

    if (cleanUserWords.length === 0) {
        return <span className="font-medium text-foreground">{resultTitle}</span>;
    }

    const escapedWords = cleanUserWords.map((word) =>
        word.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
    );

    const pattern = new RegExp(`\\b(${escapedWords.join('|')})\\b`, 'gi');
    const parts = resultTitle.split(pattern);

    return (
        <span className="leading-snug font-medium text-foreground">
            {parts.map((part, index) => {
                const isMatch = cleanUserWords.some(
                    (w) => w === part.toLowerCase()
                );

                return isMatch ? (
                    <span
                        key={index}
                        className="bg-primary/10 border-b border-primary/30 px-0.5 text-foreground dark:bg-primary/20 dark:border-primary/50"
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
        <CardContent className="p-4 sm:p-5 flex flex-col gap-4">
            <div className="flex gap-3">
                <span className="flex size-5 shrink-0 items-center justify-center rounded-none bg-muted text-[10px] font-bold text-muted-foreground/90">
                    {index + 1}
                </span>
                <div className="min-w-0 flex-1 space-y-1">
                    <h3 className="text-xs sm:text-sm leading-relaxed">
                        {highlightMatchingWords(item.judul, userTitle)}
                    </h3>
                    <div className="text-[10px] text-muted-foreground/90 font-medium">
                        {item.nama_mahasiswa || '—'}
                        {item.student_id ? ` | ${item.student_id}` : ''}
                    </div>
                </div>
            </div>
            <SimilarityBar
                persen={item.similarity_persen}
                level={item.level}
            />
        </CardContent>
    );

    return (
        <Card className="overflow-hidden border border-border bg-card rounded-none shadow-none hover:bg-muted/5 transition-colors">
            {item.student_id ? (
                <Link
                    href={`/skripsi/${item.student_id}`}
                    className="block outline-none focus-visible:ring-1 focus-visible:ring-primary"
                >
                    {content}
                </Link>
            ) : (
                content
            )}
        </Card>
    );
}
