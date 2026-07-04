import { useHttp } from '@inertiajs/react';
import { Turnstile } from '@marsidev/react-turnstile';
import {
    Loader2,
    Sparkles,
    Trash2,
    Check,
    ShieldAlert,
    Info,
} from 'lucide-react';
import { useRef, useState, useEffect } from 'react';
import { SeoHead } from '@/components/common/SeoHead';
import { PublicPageHero } from '@/components/layout/PublicPageHero';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { SimilarityResultsSection } from '@/features/similarity/components/SimilarityResultsSection';
import { cn } from '@/lib/utils';
import similarityRoute from '@/routes/similarity';
import type { TurnstileInstance } from '@marsidev/react-turnstile';
import type { SubmitEvent } from 'react';
import type { SimilarityResult } from '@/features/similarity/types';

const SKRIPSI_EXAMPLES = [
    'Penerapan Algoritma K-Nearest Neighbors untuk Klasifikasi Kelayakan Kredit',
    'Analisis Sentimen Data Ulasan Aplikasi Ruang Baca Menggunakan BERT',
    'Rancang Bangun Sistem Pendeteksi Hama Padi Berbasis Internet of Things',
    'Implementasi Kriptografi AES dan Steganografi LSB untuk Keamanan Data',
];

const KP_EXAMPLES = [
    'Rancang Bangun Sistem Informasi Logistik Menggunakan Framework Laravel',
    'Sistem Manajemen Perpustakaan Berbasis Web Terintegrasi API WhatsApp',
    'Pengembangan Aplikasi E-Commerce Toko Baju Menggunakan React Native',
    'Implementasi Sistem Monitoring Suhu Server Menggunakan Raspberry Pi',
];

const SCANNING_STEPS = [
    'Menganalisis kata kunci...',
    'Koneksi pencocokan semantik...',
    'Membandingkan indeks dokumen...',
    'Menghitung persentase final...',
];

export default function SimilarityPage({
    turnstileEnabled,
    turnstileSiteKey,
}: {
    turnstileEnabled: boolean;
    turnstileSiteKey: string;
}) {
    const [result, setResult] = useState<SimilarityResult | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [scanStep, setScanStep] = useState(0);

    const resultsRef = useRef<HTMLDivElement>(null);
    const turnstileRef = useRef<TurnstileInstance | null>(null);

    const http = useHttp({
        judul: '',
        document_type: 'skripsi',
        'cf-turnstile-response': null as string | null,
    });

    const loading = http.processing;
    const title = http.data.judul;

    const words = title.trim().split(/\s+/).filter(Boolean);
    const wordCount = words.length;
    const charCount = title.trim().length;
    const isWordCountValid = wordCount >= 5;
    const isCharCountValid = charCount >= 5;

    useEffect(() => {
        if (!loading) {
            return;
        }

        const interval = setInterval(() => {
            setScanStep((prev) => {
                if (prev < SCANNING_STEPS.length - 1) {
                    return prev + 1;
                }

                return prev;
            });
        }, 800);

        return () => clearInterval(interval);
    }, [loading]);

    const handleSubmit = (e: SubmitEvent) => {
        e.preventDefault();

        if (!title || charCount < 5) {
            setError('Judul harus terdiri dari minimal 5 karakter.');

            return;
        }

        if (!isWordCountValid) {
            setError('Masukkan minimal 5 kata agar hasil lebih akurat.');

            return;
        }

        if (turnstileEnabled && !http.data['cf-turnstile-response']) {
            setError('Silakan selesaikan verifikasi keamanan.');

            return;
        }

        setError(null);
        setResult(null);
        setScanStep(0);

        http.post(similarityRoute.check.url(), {
            onSuccess: (data: unknown) => {
                setResult(data as SimilarityResult);
                setTimeout(() => {
                    resultsRef.current?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
                }, 100);
            },
            onError: (err: unknown) => {
                setError(
                    err instanceof Error
                        ? err.message
                        : 'Terjadi kesalahan saat memeriksa kemiripan.',
                );
            },
            onFinish: () => {
                http.setData('cf-turnstile-response', null);
                turnstileRef.current?.reset();
            }
        });
    };

    const handleExampleClick = (exampleText: string) => {
        if (loading) {
            return;
        }

        http.setData('judul', exampleText);
        setError(null);
    };

    return (
        <>
            <SeoHead
                title="Cek Kemiripan Judul"
                description="Periksa kemiripan judul skripsi atau laporan kerja praktek sebelum diajukan."
            />
            <div className="relative z-10 flex flex-1 flex-col">
                <PublicPageHero
                    title="Cek Kemiripan Judul"
                    description="Pemeriksaan awal untuk membantu meninjau kemiripan judul skripsi atau laporan kerja praktek Anda."
                    contentClassName="max-w-7xl px-4 sm:px-6 lg:px-8"
                />

                {/* Centered layout container */}
                <div className="mx-auto w-full max-w-4xl px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
                    <div className="space-y-6">
                            <Card className="rounded-2xl border-border bg-card shadow-sm">
                                <CardContent className="p-5 sm:p-6">
                                    <form
                                        onSubmit={handleSubmit}
                                        className="space-y-5"
                                    >
                                        {/* Tipe Dokumen Selector */}
                                        <div className="flex gap-2">
                                            <button
                                                type="button"
                                                onClick={() => http.setData('document_type', 'skripsi')}
                                                className={cn(
                                                    "flex-1 py-2 px-3 text-xs font-medium transition-all border rounded-xl cursor-pointer",
                                                    http.data.document_type === 'skripsi'
                                                        ? "bg-primary text-primary-foreground border-primary shadow-sm"
                                                        : "bg-card text-muted-foreground border-border hover:bg-muted/5 hover:text-foreground"
                                                )}
                                                disabled={loading}
                                            >
                                                Skripsi
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => http.setData('document_type', 'internship_report')}
                                                className={cn(
                                                    "flex-1 py-2 px-3 text-xs font-medium transition-all border rounded-xl cursor-pointer",
                                                    http.data.document_type === 'internship_report'
                                                        ? "bg-primary text-primary-foreground border-primary shadow-sm"
                                                        : "bg-card text-muted-foreground border-border hover:bg-muted/5 hover:text-foreground"
                                                )}
                                                disabled={loading}
                                            >
                                                Laporan Kerja Praktek
                                            </button>
                                        </div>

                                        <div className="space-y-3">
                                            <div className="flex items-center justify-between">
                                                <span className="text-xs font-medium text-foreground">
                                                    Draf Judul {http.data.document_type === 'skripsi' ? 'Skripsi' : 'Laporan Kerja Praktek'}
                                                </span>
                                                {title && (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            http.setData('judul', '')
                                                        }
                                                        className="flex items-center gap-1 text-[10px] font-medium text-muted-foreground/80 hover:text-destructive"
                                                        disabled={loading}
                                                    >
                                                        <Trash2 className="size-3" />
                                                        Bersihkan
                                                     </button>
                                                )}
                                            </div>

                                            <div className="space-y-2">
                                                <Label
                                                    htmlFor="judul"
                                                    className="sr-only"
                                                >
                                                    Draf judul {http.data.document_type === 'skripsi' ? 'skripsi' : 'laporan kerja praktek'}
                                                </Label>
                                                <Textarea
                                                    id="judul"
                                                    placeholder={http.data.document_type === 'skripsi' ? "Klasifikasi Sentimen Ulasan..." : "Sistem Informasi Penjualan..."}
                                                    value={title}
                                                    onChange={(e) =>
                                                        http.setData('judul', e.target.value)
                                                    }
                                                    className="min-h-28 resize-y rounded-xl border-border bg-transparent p-4 text-xs leading-relaxed shadow-none focus-visible:border-primary focus-visible:ring-0 focus-visible:ring-offset-0 sm:text-sm"
                                                    disabled={loading}
                                                    autoComplete="off"
                                                />
                                            </div>

                                            {/* Quality Indicators */}
                                            <div className="flex flex-wrap items-center gap-4 border-t border-border/60 pt-3 text-[10px]">
                                                <div className="flex items-center gap-1.5">
                                                    <span
                                                        className={`inline-flex size-3.5 items-center justify-center rounded-full text-[9px] ${
                                                            isWordCountValid
                                                                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                                                : 'bg-muted text-muted-foreground/80'
                                                        }`}
                                                    >
                                                        {isWordCountValid ? (
                                                            <Check className="size-2.5" />
                                                        ) : (
                                                            '1'
                                                        )}
                                                    </span>
                                                    <span
                                                        className={
                                                            isWordCountValid
                                                                ? 'font-medium text-emerald-600 dark:text-emerald-400'
                                                                : 'font-medium text-muted-foreground/80'
                                                        }
                                                    >
                                                        Min. 5 kata ({wordCount}
                                                        )
                                                    </span>
                                                </div>
                                                <div className="flex items-center gap-1.5">
                                                    <span
                                                        className={`inline-flex size-3.5 items-center justify-center rounded-full text-[9px] ${
                                                            isCharCountValid
                                                                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                                                : 'bg-muted text-muted-foreground/80'
                                                        }`}
                                                    >
                                                        {isCharCountValid ? (
                                                            <Check className="size-2.5" />
                                                        ) : (
                                                            '2'
                                                        )}
                                                    </span>
                                                    <span
                                                        className={
                                                            isCharCountValid
                                                                ? 'font-medium text-emerald-600 dark:text-emerald-400'
                                                                : 'font-medium text-muted-foreground/80'
                                                        }
                                                    >
                                                        Min. 5 karakter (
                                                        {charCount})
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="flex flex-col gap-4 pt-2 sm:flex-row sm:items-center sm:justify-between">
                                            {turnstileEnabled &&
                                                turnstileSiteKey && (
                                                    <div className="flex min-h-[65px] shrink-0 items-center justify-center sm:justify-start">
                                                        <Turnstile
                                                            ref={turnstileRef}
                                                            siteKey={
                                                                turnstileSiteKey
                                                            }
                                                            onSuccess={(
                                                                token,
                                                            ) =>
                                                                http.setData(
                                                                    'cf-turnstile-response',
                                                                    token,
                                                                )
                                                            }
                                                            onExpire={() =>
                                                                http.setData(
                                                                    'cf-turnstile-response',
                                                                    null,
                                                                )
                                                            }
                                                            onError={() =>
                                                                http.setData(
                                                                    'cf-turnstile-response',
                                                                    null,
                                                                )
                                                            }
                                                            options={{
                                                                size: 'normal',
                                                                theme: 'dark',
                                                            }}
                                                        />
                                                    </div>
                                                )}

                                            <Button
                                                id="btn-cek-kemiripan"
                                                type="submit"
                                                disabled={
                                                    loading || !title.trim()
                                                }
                                                className="flex h-12 w-full cursor-pointer items-center justify-center gap-2 rounded-xl px-6 text-sm font-medium sm:ml-auto sm:w-auto"
                                            >
                                                {loading ? (
                                                    <Loader2 className="size-4 animate-spin" />
                                                ) : (
                                                    <Sparkles className="size-4" />
                                                )}
                                                {loading
                                                    ? 'Menganalisis...'
                                                    : 'Mulai Analisis'}
                                            </Button>
                                        </div>

                                        {error ? (
                                            <div className="flex items-start gap-2 rounded-xl border border-destructive/20 bg-destructive/5 px-3 py-2.5 text-xs text-destructive">
                                                <ShieldAlert className="mt-0.5 size-3.5 shrink-0" />
                                                <span>{error}</span>
                                            </div>
                                        ) : null}
                                    </form>
                                </CardContent>
                            </Card>

                            {/* Examples */}
                            <div className="space-y-3">
                                <span className="flex items-center gap-1.5 text-xs font-medium text-muted-foreground/90">
                                    <Info className="size-3.5 text-primary/70" />
                                    Contoh Judul {http.data.document_type === 'skripsi' ? 'Skripsi' : 'Laporan Kerja Praktek'}
                                </span>
                                <div className="grid gap-2 sm:grid-cols-2">
                                    {(http.data.document_type === 'skripsi' ? SKRIPSI_EXAMPLES : KP_EXAMPLES).map((example, idx) => (
                                        <button
                                            key={idx}
                                            type="button"
                                            onClick={() =>
                                                handleExampleClick(example)
                                            }
                                            className="line-clamp-2 cursor-pointer rounded-xl border border-border bg-card p-3 text-left text-xs font-medium text-muted-foreground/90 transition-colors hover:bg-muted/5 hover:text-foreground"
                                            disabled={loading}
                                        >
                                            {example}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {/* Loading State */}
                            {loading && (
                                <Card className="rounded-2xl border border-border bg-card shadow-sm">
                                    <CardContent className="flex flex-col items-center space-y-6 p-6 text-center">
                                        <div className="flex size-12 items-center justify-center rounded-xl border border-border bg-muted/5 text-primary">
                                            <Loader2 className="size-6 animate-spin text-muted-foreground/85" />
                                        </div>
                                        <div className="space-y-1">
                                            <h3 className="text-sm font-medium text-foreground">
                                                Menganalisis Judul {http.data.document_type === 'skripsi' ? 'Skripsi' : 'Laporan Kerja Praktek'}...
                                            </h3>
                                            <p className="max-w-md text-xs text-muted-foreground/90">
                                                Memproses kecocokan semantik
                                                dengan database Ruang Baca
                                                Informatika.
                                            </p>
                                        </div>
                                        <div className="w-full max-w-sm space-y-2.5 rounded-xl border border-border/60 bg-muted/5 p-3.5 text-left">
                                            {SCANNING_STEPS.map((step, idx) => {
                                                const isDone = scanStep > idx;
                                                const isActive =
                                                    scanStep === idx;

                                                return (
                                                    <div
                                                        key={idx}
                                                        className="flex items-center gap-3"
                                                    >
                                                        {isDone ? (
                                                            <span className="flex size-4.5 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                                                <Check className="size-3 stroke-[3px]" />
                                                            </span>
                                                        ) : isActive ? (
                                                            <span className="flex size-4.5 shrink-0 items-center justify-center rounded-full bg-muted text-muted-foreground">
                                                                <Loader2 className="size-2.5 animate-spin" />
                                                            </span>
                                                        ) : (
                                                            <span className="flex size-4.5 shrink-0 items-center justify-center rounded-full border border-border text-[9px] font-medium text-muted-foreground/80">
                                                                {idx + 1}
                                                            </span>
                                                        )}
                                                        <span
                                                            className={`text-xs ${
                                                                isDone
                                                                    ? 'text-muted-foreground/80 line-through decoration-muted-foreground/20'
                                                                    : isActive
                                                                      ? 'font-medium text-foreground'
                                                                      : 'text-muted-foreground/60'
                                                            }`}
                                                        >
                                                            {step}
                                                        </span>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </CardContent>
                                </Card>
                            )}

                            {/* Results Output */}
                            {result && !loading && (
                                <div
                                    ref={resultsRef}
                                    className="scroll-mt-24 space-y-4"
                                >
                                    <div className="flex items-center justify-between border-b border-border/60 pb-3">
                                        <h2 className="text-sm font-medium text-foreground">
                                            Hasil Analisis Kemiripan
                                        </h2>
                                        <span className="text-[10px] font-medium text-muted-foreground/80">
                                            Selesai ({result.total_found}{' '}
                                            Temuan)
                                        </span>
                                    </div>
                                    <SimilarityResultsSection
                                        result={result}
                                        userTitle={title}
                                    />
                                </div>
                            )}
                    </div>
                </div>
            </div>
        </>
    );
}
