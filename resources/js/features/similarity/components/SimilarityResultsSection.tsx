import { AlertCircle, ShieldCheck } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { SimilarityResultCard } from '@/features/similarity/components/SimilarityResultCard';
import type { SimilarityResult } from '@/features/similarity/types';

function SafeResult() {
    return (
        <Card className="border-emerald-200 bg-emerald-50/60 dark:border-emerald-900 dark:bg-emerald-950/20">
            <CardContent className="flex items-center gap-5 py-8">
                <div className="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 shadow-sm dark:bg-emerald-900/40">
                    <ShieldCheck className="size-7 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div>
                    <h3 className="text-lg font-bold text-emerald-900 dark:text-emerald-100">
                        Judul Aman Digunakan
                    </h3>
                    <p className="mt-0.5 text-sm text-emerald-700 dark:text-emerald-300">
                        Tidak ditemukan kemiripan yang signifikan dengan judul
                        yang ada dalam database perpustakaan.
                    </p>
                </div>
            </CardContent>
        </Card>
    );
}

interface SimilarityResultsSectionProps {
    result: SimilarityResult;
}

function ResultsSummaryBanner({ result }: SimilarityResultsSectionProps) {
    const counts = result.results.reduce((acc, r) => {
        const level = r.level.toUpperCase();
        acc[level] = (acc[level] || 0) + 1;

        return acc;
    }, {} as Record<string, number>);

    return (
        <div className="flex flex-wrap items-center justify-between gap-4 rounded-2xl border bg-card/50 backdrop-blur-sm px-6 py-4 shadow-sm">
            <div className="flex items-center gap-3">
                <div className="flex size-10 items-center justify-center rounded-full bg-primary/10">
                    <AlertCircle className="size-5 text-primary" />
                </div>
                <div className="flex flex-col">
                    <span className="text-sm font-medium text-muted-foreground">Total Temuan</span>
                    <span className="text-lg font-bold leading-none">
                        {result.total_found} Kemiripan
                    </span>
                </div>
            </div>
            <div className="flex flex-wrap gap-2">
                {counts['SANGAT TINGGI'] > 0 && (
                    <Badge variant="outline" className="border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900/50 dark:bg-rose-900/20 dark:text-rose-400 font-semibold px-2.5 py-1">
                        {counts['SANGAT TINGGI']} Sangat Tinggi
                    </Badge>
                )}
                {counts['TINGGI'] > 0 && (
                    <Badge variant="outline" className="border-orange-200 bg-orange-50 text-orange-700 dark:border-orange-900/50 dark:bg-orange-900/20 dark:text-orange-400 font-semibold px-2.5 py-1">
                        {counts['TINGGI']} Tinggi
                    </Badge>
                )}
                {counts['SEDANG'] > 0 && (
                    <Badge variant="outline" className="border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/50 dark:bg-amber-900/20 dark:text-amber-400 font-semibold px-2.5 py-1">
                        {counts['SEDANG']} Sedang
                    </Badge>
                )}
                {counts['RENDAH'] > 0 && (
                    <Badge variant="outline" className="border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-900/20 dark:text-emerald-400 font-semibold px-2.5 py-1">
                        {counts['RENDAH']} Rendah
                    </Badge>
                )}
                {counts['SANGAT RENDAH'] > 0 && (
                    <Badge variant="outline" className="border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900/50 dark:bg-blue-900/20 dark:text-blue-400 font-semibold px-2.5 py-1">
                        {counts['SANGAT RENDAH']} Sangat Rendah
                    </Badge>
                )}
            </div>
        </div>
    );
}

export function SimilarityResultsSection({
    result,
}: SimilarityResultsSectionProps) {
    if (result.total_found === 0) {
        return <SafeResult />;
    }

    return (
        <div className="animate-in space-y-5 duration-500 fade-in slide-in-from-bottom-4">
            <ResultsSummaryBanner result={result} />
            <div className="grid gap-3">
                {result.results.map((item, index) => (
                    <SimilarityResultCard key={index} item={item} index={index} />
                ))}
            </div>
        </div>
    );
}
