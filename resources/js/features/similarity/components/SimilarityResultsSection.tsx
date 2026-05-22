import { AlertCircle, ShieldCheck, FileText } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { SimilarityResultCard } from '@/features/similarity/components/SimilarityResultCard';
import type { SimilarityResult } from '@/features/similarity/types';

function SafeResult() {
    return (
        <Card className="animate-in border-primary/20 bg-linear-to-b from-primary/5 to-transparent shadow-lg duration-500 fade-in slide-in-from-bottom-4 dark:border-primary/15">
            <CardContent className="flex flex-col items-center space-y-4 p-8 text-center sm:p-10">
                <div className="relative flex size-20 items-center justify-center">
                    {/* Glowing shield pulse */}
                    <div className="absolute inset-0 animate-ping rounded-full bg-primary/10 opacity-75" />
                    <div className="absolute inset-2 animate-pulse rounded-full bg-primary/20" />
                    <div className="relative flex size-14 items-center justify-center rounded-2xl border border-primary/20 bg-primary/10 shadow-sm dark:border-primary/20 dark:bg-primary/15">
                        <ShieldCheck className="size-8 text-primary" />
                    </div>
                </div>

                <div className="max-w-xl space-y-2">
                    <h3 className="text-xl font-bold text-foreground">
                        Judul Aman & Unik!
                    </h3>
                    <p className="text-sm leading-relaxed text-muted-foreground">
                        Tidak ditemukan kemiripan judul yang signifikan dalam
                        database Ruang Baca Informatika. Judul ini memiliki
                        tingkat orisinalitas tinggi dan siap diajukan.
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
    const counts = result.results.reduce(
        (acc, r) => {
            const level = r.level.toUpperCase();
            acc[level] = (acc[level] || 0) + 1;

            return acc;
        },
        {} as Record<string, number>,
    );

    // Calculate maximum similarity percentage
    const maxPercent =
        result.results.length > 0
            ? Math.max(...result.results.map((r) => r.similarity_persen))
            : 0;

    // Get color and severity warning for max percent
    let maxConfig = {
        label: 'Aman',
        colorClass: 'text-emerald-500',
        strokeClass: 'stroke-emerald-500',
        bgClass:
            'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/50',
        desc: 'Tingkat kemiripan sangat rendah. Judul aman digunakan.',
    };

    if (maxPercent >= 80) {
        maxConfig = {
            label: 'Sangat Tinggi',
            colorClass: 'text-rose-500',
            strokeClass: 'stroke-rose-500',
            bgClass:
                'bg-rose-50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/50',
            desc: 'Kemiripan sangat kritis. Judul sebaiknya diubah/dimodifikasi secara signifikan.',
        };
    } else if (maxPercent >= 60) {
        maxConfig = {
            label: 'Tinggi',
            colorClass: 'text-orange-500',
            strokeClass: 'stroke-orange-500',
            bgClass:
                'bg-orange-50 dark:bg-orange-950/20 border-orange-200 dark:border-orange-900/50',
            desc: 'Kemiripan tinggi. Pertimbangkan untuk memvariasikan metode atau objek penelitian.',
        };
    } else if (maxPercent >= 40) {
        maxConfig = {
            label: 'Sedang',
            colorClass: 'text-amber-500',
            strokeClass: 'stroke-amber-500',
            bgClass:
                'bg-amber-50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-900/50',
            desc: 'Kemiripan sedang. Pastikan kontribusi penelitian (novelty) dijelaskan dengan jelas.',
        };
    } else if (maxPercent > 0) {
        maxConfig = {
            label: 'Rendah',
            colorClass: 'text-blue-500',
            strokeClass: 'stroke-blue-500',
            bgClass:
                'bg-blue-50 dark:bg-blue-950/20 border-blue-200 dark:border-blue-900/50',
            desc: 'Kemiripan rendah. Aman dengan sedikit penyesuaian redaksional.',
        };
    }

    // Radial Gauge SVG Parameters
    const radius = 28;
    const strokeWidth = 5;
    const circumference = 2 * Math.PI * radius;
    const strokeDashoffset = circumference - (maxPercent / 100) * circumference;

    return (
        <div className="grid gap-4 sm:grid-cols-3">
            {/* Max Similarity Gauge Card */}
            <div
                className={`flex flex-col items-center gap-6 rounded-2xl border px-6 py-6 shadow-sm backdrop-blur-xs sm:col-span-2 sm:flex-row ${maxConfig.bgClass}`}
            >
                {/* Gauge SVG */}
                <div className="relative flex size-24 shrink-0 items-center justify-center">
                    <svg className="size-24 -rotate-90 transform">
                        {/* Background track circle */}
                        <circle
                            cx="48"
                            cy="48"
                            r={radius}
                            className="fill-none stroke-muted"
                            strokeWidth={strokeWidth}
                        />
                        {/* Value arc indicator */}
                        {maxPercent > 0 && (
                            <circle
                                cx="48"
                                cy="48"
                                r={radius}
                                className={`fill-none transition-all duration-1000 ease-out ${maxConfig.strokeClass}`}
                                strokeWidth={strokeWidth}
                                strokeDasharray={circumference}
                                strokeDashoffset={strokeDashoffset}
                                strokeLinecap="round"
                            />
                        )}
                    </svg>
                    <div className="absolute inset-0 flex flex-col items-center justify-center">
                        <span className="text-xl font-black tracking-tight">
                            {maxPercent}%
                        </span>
                        <span className="text-[9px] font-bold tracking-wider text-muted-foreground uppercase">
                            Maksimal
                        </span>
                    </div>
                </div>

                <div className="flex-1 space-y-2 text-center sm:text-left">
                    <div className="flex flex-col justify-center gap-2 sm:flex-row sm:items-center sm:justify-start">
                        <span className="text-sm font-semibold text-muted-foreground">
                            Tingkat Kemiripan:
                        </span>
                        <span
                            className={`text-sm font-bold tracking-wide uppercase ${maxConfig.colorClass}`}
                        >
                            {maxConfig.label}
                        </span>
                    </div>
                    <p className="text-xs leading-relaxed text-muted-foreground">
                        {maxConfig.desc}
                    </p>
                </div>
            </div>

            {/* Total Count & Breakdown Card */}
            <div className="flex flex-col justify-between rounded-2xl border bg-card/60 px-5 py-5 shadow-sm backdrop-blur-xs">
                <div className="flex items-center justify-between border-b border-border/40 pb-3">
                    <div className="flex items-center gap-2">
                        <div className="flex size-7 items-center justify-center rounded-full bg-primary/10">
                            <AlertCircle className="size-4 text-primary" />
                        </div>
                        <span className="text-xs font-semibold text-muted-foreground">
                            Total Temuan
                        </span>
                    </div>
                    <span className="text-sm font-bold tabular-nums">
                        {result.total_found} skripsi
                    </span>
                </div>

                {/* Category breakdown badges */}
                <div className="flex flex-wrap gap-1.5 pt-3">
                    {counts['SANGAT TINGGI'] > 0 && (
                        <Badge
                            variant="outline"
                            className="border-rose-200 bg-rose-50 py-0.5 text-[10px] font-bold text-rose-700 dark:border-rose-900/50 dark:bg-rose-900/20 dark:text-rose-400"
                        >
                            {counts['SANGAT TINGGI']} Kritis
                        </Badge>
                    )}
                    {counts['TINGGI'] > 0 && (
                        <Badge
                            variant="outline"
                            className="border-orange-200 bg-orange-50 py-0.5 text-[10px] font-bold text-orange-700 dark:border-orange-900/50 dark:bg-orange-900/20 dark:text-orange-400"
                        >
                            {counts['TINGGI']} Tinggi
                        </Badge>
                    )}
                    {counts['SEDANG'] > 0 && (
                        <Badge
                            variant="outline"
                            className="border-amber-200 bg-amber-50 py-0.5 text-[10px] font-bold text-amber-700 dark:border-amber-900/50 dark:bg-amber-900/20 dark:text-amber-400"
                        >
                            {counts['SEDANG']} Sedang
                        </Badge>
                    )}
                    {counts['RENDAH'] > 0 && (
                        <Badge
                            variant="outline"
                            className="border-emerald-200 bg-emerald-50 py-0.5 text-[10px] font-bold text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-900/20 dark:text-emerald-400"
                        >
                            {counts['RENDAH']} Rendah
                        </Badge>
                    )}
                    {counts['SANGAT RENDAH'] > 0 && (
                        <Badge
                            variant="outline"
                            className="border-blue-200 bg-blue-50 py-0.5 text-[10px] font-bold text-blue-700 dark:border-blue-900/50 dark:bg-blue-900/20 dark:text-blue-400"
                        >
                            {counts['SANGAT RENDAH']} Sangat Rendah
                        </Badge>
                    )}
                </div>
            </div>
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
        <div className="animate-in space-y-6 duration-500 fade-in slide-in-from-bottom-4">
            <ResultsSummaryBanner result={result} />
            <div className="space-y-3">
                <div className="space-y-1 px-1">
                    <h3 className="flex items-center gap-2 text-base font-semibold text-foreground">
                        <FileText className="size-4 text-primary" />
                        Koleksi skripsi yang mirip
                    </h3>
                    <p className="text-sm text-muted-foreground">
                        Berikut daftar judul dengan tingkat kemiripan paling
                        relevan terhadap draf yang Anda masukkan.
                    </p>
                </div>

                <div className="grid gap-3.5">
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
        </div>
    );
}
