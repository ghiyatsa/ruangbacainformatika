import { ShieldCheck } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { SimilarityResultCard } from '@/features/similarity/components/SimilarityResultCard';
import { getLevelConfig } from '@/features/similarity/types';
import type { SimilarityResult } from '@/features/similarity/types';

function SafeResult() {
    return (
        <Card className="border-border/60 bg-card/95 shadow-sm">
            <CardContent className="flex flex-col items-center gap-4 p-8 text-center sm:p-10">
                <div className="flex size-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">
                    <ShieldCheck className="size-5" />
                </div>
                <div className="max-w-xl space-y-2">
                    <h3 className="text-lg font-semibold text-foreground">
                        Tidak ada kemiripan yang menonjol
                    </h3>
                    <p className="text-sm leading-6 text-muted-foreground">
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
        <div className="gap-4">
            <Card className="border-border/60 bg-card/95 shadow-sm">
                <CardContent className="space-y-5 p-5 sm:p-6">
                    <div className="space-y-3">
                        <p className="text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase">
                            Ringkasan hasil
                        </p>
                        <div className="flex flex-wrap items-center gap-3">
                            <span className="text-4xl font-semibold text-foreground">
                                {maxPercent}%
                            </span>
                            <Badge
                                variant="outline"
                                className={`h-7 rounded-full px-3 text-[11px] font-semibold uppercase ${topLevelConfig.badgeClass}`}
                            >
                                {topLevelConfig.label}
                            </Badge>
                        </div>
                        <p className="max-w-xl text-sm leading-6 text-muted-foreground">
                            {maxDescription}
                        </p>
                    </div>

                    <div className="space-y-2">
                        <div className="text-xs text-muted-foreground">
                            Kemiripan tertinggi
                        </div>
                        <div className="h-2 overflow-hidden rounded-full bg-muted">
                            <div
                                className="h-full rounded-full bg-foreground/80"
                                style={{ width: `${maxPercent}%` }}
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
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
        <div className="space-y-6">
            <ResultsSummaryBanner result={result} />
            <section>
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
            </section>
        </div>
    );
}
