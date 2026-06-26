import { ShieldCheck } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { SimilarityResultCard } from '@/features/similarity/components/SimilarityResultCard';
import { getLevelConfig } from '@/features/similarity/types';
import { cn } from '@/lib/utils';
import type { SimilarityResult } from '@/features/similarity/types';

function SafeResult() {
    return (
        <Card className="border border-border bg-card rounded-none shadow-none">
            <CardContent className="flex flex-col items-center gap-4 p-8 text-center sm:p-10">
                <div className="flex size-10 items-center justify-center rounded-none border border-emerald-500/20 bg-emerald-500/5 text-emerald-600 dark:text-emerald-400">
                    <ShieldCheck className="size-4" />
                </div>
                <div className="max-w-xl space-y-2">
                    <h3 className="text-sm font-bold text-foreground">
                        Tidak ada kemiripan yang menonjol
                    </h3>
                    <p className="text-xs leading-relaxed text-muted-foreground/90">
                        Tidak ditemukan judul yang sangat dekat pada data yang
                        sedang diperiksa. Tetap tinjau fokus, objek, dan metode
                        penelitian sebelum diajukan.
                    </p>
                </div>
            </CardContent>
        </Card>
    );
}

interface SimilarityResultsSectionProps {
    result: SimilarityResult;
    userTitle?: string;
}

function ResultsSummaryBanner({ result }: SimilarityResultsSectionProps) {
    const topResult = result.results.reduce((highest, current) => {
        if (!highest) {
            return current;
        }

        return current.similarity_persen > highest.similarity_persen
            ? current
            : highest;
    }, result.results[0]);

    const maxPercent = topResult?.similarity_persen ?? 0;

    const topLevelConfig = topResult
        ? getLevelConfig(topResult.level)
        : getLevelConfig('RENDAH');

    const levelDescriptions: Record<string, string> = {
        'SANGAT TINGGI':
            'Kemiripan sangat tinggi. Judul sebaiknya diubah sebelum diajukan.',
        TINGGI: 'Kemiripan tinggi. Pertimbangkan perubahan pada fokus, objek, atau metode.',
        SEDANG: 'Kemiripan sedang. Pastikan pembeda penelitian Anda cukup jelas.',
        RENDAH: 'Kemiripan rendah. Tinjau ulang redaksi judul sebelum final.',
        'SANGAT RENDAH':
            'Kemiripan sangat rendah. Judul relatif aman untuk dilanjutkan.',
    };

    const maxDescription =
        levelDescriptions[topResult?.level.toUpperCase() ?? ''] ??
        'Kemiripan rendah. Judul relatif aman untuk dilanjutkan.';

    return (
        <Card className="border border-border bg-card rounded-none shadow-none">
            <CardContent className="space-y-4 p-4 sm:p-5">
                <div className="space-y-2">
                    <p className="text-[10px] font-bold tracking-widest text-muted-foreground/80 uppercase">
                        Ringkasan Hasil
                    </p>
                    <div className="flex flex-wrap items-center gap-3">
                        <span className="text-3xl font-extrabold text-foreground tracking-tight">
                            {maxPercent}%
                        </span>
                        <Badge
                            variant="outline"
                            className={cn(
                                'h-6 rounded-full px-2.5 text-[9px] font-bold uppercase transition-none shadow-none',
                                topLevelConfig.badgeClass
                            )}
                        >
                            {topLevelConfig.label}
                        </Badge>
                    </div>
                    <p className="max-w-xl text-xs leading-relaxed text-muted-foreground/90">
                        {maxDescription}
                    </p>
                </div>

                <div className="space-y-1.5">
                    <div className="text-[9px] font-bold tracking-widest text-muted-foreground/80 uppercase">
                        Kemiripan Tertinggi
                    </div>
                    <div className="h-1.5 overflow-hidden bg-muted rounded-none">
                        <div
                            className={cn('h-full', topLevelConfig.bg)}
                            style={{ width: `${maxPercent}%` }}
                        />
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}

export function SimilarityResultsSection({
    result,
    userTitle = '',
}: SimilarityResultsSectionProps) {
    if (result.total_found === 0) {
        return <SafeResult />;
    }

    return (
        <div className="space-y-4">
            <ResultsSummaryBanner result={result} />
            <div className="grid gap-3">
                {result.results.map((item, index) => (
                    <SimilarityResultCard
                        key={index}
                        item={item}
                        index={index}
                        userTitle={userTitle}
                    />
                ))}
            </div>
        </div>
    );
}
