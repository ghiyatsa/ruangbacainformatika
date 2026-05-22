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
                    className={`h-full rounded-full transition-all duration-1000 ease-out ${cfg.bg}`}
                    style={{ width: `${persen}%` }}
                />
            </div>
            <span
                className={`w-12 text-right text-xs font-extrabold tabular-nums ${cfg.color}`}
            >
                {persen}%
            </span>
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
                    <mark
                        key={index}
                        className="rounded border-b border-amber-400 bg-amber-100/90 px-1 py-0.5 font-bold text-amber-900 transition-colors dark:border-amber-700 dark:bg-amber-950/60 dark:text-amber-300"
                    >
                        {part}
                    </mark>
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
    const cfg = getLevelConfig(item.level);
    const LevelIcon = cfg.icon;

    const Content = (
        <div className="flex flex-col gap-4 p-5">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div className="min-w-0 flex-1 space-y-2">
                    <div className="flex items-start gap-3">
                        <span className="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-muted text-[10px] font-bold text-muted-foreground transition-colors group-hover:bg-primary/10 group-hover:text-primary">
                            {index + 1}
                        </span>
                        <div className="flex-1 space-y-1.5">
                            <h3 className="transition-colors group-hover:text-primary">
                                {highlightMatchingWords(item.judul, userTitle)}
                            </h3>
                            <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
                                <User className="size-3.5 shrink-0 text-muted-foreground/75" />
                                <span className="font-medium">
                                    {item.nama_mahasiswa || 'Tidak diketahui'}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="flex shrink-0 items-center gap-2 pl-9 sm:pl-0">
                    <Badge
                        variant="outline"
                        className={`gap-1 px-2 py-0.5 text-[9px] font-bold tracking-wider uppercase ${cfg.badgeClass}`}
                    >
                        <LevelIcon className="size-3" />
                        {cfg.label}
                    </Badge>
                    {item.student_id && (
                        <div className="flex size-7 items-center justify-center rounded-full bg-muted/30 transition-colors group-hover:bg-primary/10">
                            <ExternalLink className="size-3.5 text-muted-foreground/60 transition-colors group-hover:text-primary" />
                        </div>
                    )}
                </div>
            </div>

            <div className="pl-9">
                <SimilarityBar
                    persen={item.similarity_persen}
                    level={item.level}
                />
            </div>
        </div>
    );

    return (
        <Card className="group relative overflow-hidden border-border/60 bg-card/95 shadow-xs transition-all duration-300 hover:-translate-y-0.5 hover:bg-card/100 hover:shadow-md">
            {/* Color accent bar on the left */}
            <div
                className={`absolute top-0 left-0 h-full w-1 transition-all duration-300 group-hover:w-1.5 ${cfg.bg}`}
            />

            {item.student_id ? (
                <Link
                    href={`/skripsi/${item.student_id}`}
                    className="block cursor-pointer outline-none ring-inset focus-visible:ring-2 focus-visible:ring-primary"
                >
                    {Content}
                </Link>
            ) : (
                Content
            )}
        </Card>
    );
}
