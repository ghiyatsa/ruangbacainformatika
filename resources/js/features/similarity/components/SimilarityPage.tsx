import { Turnstile } from '@marsidev/react-turnstile';
import type { TurnstileInstance } from '@marsidev/react-turnstile';
import {
    Loader2,
    Sparkles,
    Trash2,
    Check,
    ShieldAlert,
    BookOpen,
    Info,
    Search,
} from 'lucide-react';
import type { SubmitEvent } from 'react';
import { useRef, useState, useEffect } from 'react';
import { LibraryPageHero } from '@/components/layouts/LibraryPageHero';
import { PageLayout } from '@/components/layouts/PageLayout';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { SimilarityHowItWorks } from '@/features/similarity/components/SimilarityHowItWorks';
import { SimilarityResultsSection } from '@/features/similarity/components/SimilarityResultsSection';
import type { SimilarityResult } from '@/features/similarity/types';
import similarityRoute from '@/routes/similarity';

const EXAMPLES = [
    'Penerapan Algoritma K-Nearest Neighbors untuk Klasifikasi Kelayakan Kredit',
    'Analisis Sentimen Data Ulasan Aplikasi Ruang Baca Menggunakan BERT',
    'Rancang Bangun Sistem Pendeteksi Hama Padi Berbasis Internet of Things',
    'Implementasi Kriptografi AES dan Steganografi LSB untuk Keamanan Data',
];

const SCANNING_STEPS = [
    'Menganalisis kata kunci dan struktur kalimat...',
    'Menghubungkan ke layanan pencocokan semantik...',
    'Membandingkan dengan indeks skripsi Ruang Baca...',
    'Menghitung persentase kemiripan final...',
];

export default function SimilarityPage({
    turnstileEnabled,
    turnstileSiteKey,
}: {
    turnstileEnabled: boolean;
    turnstileSiteKey: string;
}) {
    const [title, setTitle] = useState('');
    const [loading, setLoading] = useState(false);
    const [result, setResult] = useState<SimilarityResult | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [turnstileToken, setTurnstileToken] = useState<string | null>(null);
    const [scanStep, setScanStep] = useState(0);

    const resultsRef = useRef<HTMLDivElement>(null);
    const turnstileRef = useRef<TurnstileInstance | null>(null);

    // Live word and character counting
    const words = title.trim().split(/\s+/).filter(Boolean);
    const wordCount = words.length;
    const charCount = title.trim().length;
    const isWordCountValid = wordCount >= 5;
    const isCharCountValid = charCount >= 5;

    // Simulate scanning phases during loading
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

    const handleSubmit = async (e: SubmitEvent) => {
        e.preventDefault();

        if (!title || charCount < 5) {
            setError('Judul harus terdiri dari minimal 5 karakter.');

            return;
        }

        if (!isWordCountValid) {
            setError('Masukkan minimal 5 kata agar hasil lebih akurat.');

            return;
        }

        if (turnstileEnabled && !turnstileToken) {
            setError('Silakan selesaikan verifikasi keamanan.');

            return;
        }

        setLoading(true);
        setError(null);
        setResult(null);
        setScanStep(0);

        try {
            const response = await fetch(similarityRoute.check.url(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    judul: title,
                    'cf-turnstile-response': turnstileToken,
                }),
            });

            const data = (await response.json()) as
                | SimilarityResult
                | { message?: string };

            if (!response.ok) {
                throw new Error(
                    'message' in data && data.message
                        ? data.message
                        : 'Terjadi kesalahan saat memeriksa kemiripan.',
                );
            }

            setResult(data as SimilarityResult);

            setTimeout(() => {
                resultsRef.current?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                });
            }, 100);
        } catch (err: unknown) {
            setError(
                err instanceof Error
                    ? err.message
                    : 'Terjadi kesalahan saat memeriksa kemiripan.',
            );
        } finally {
            setLoading(false);
            setTurnstileToken(null);
            turnstileRef.current?.reset();
        }
    };

    const handleExampleClick = (exampleText: string) => {
        if (loading) {
            return;
        }

        setTitle(exampleText);
        setError(null);
    };

    return (
        <PageLayout
            title="Cek Kemiripan Judul"
            metaDescription="Periksa kemiripan judul skripsi dengan koleksi Ruang Baca Teknik Informatika sebelum pengajuan proposal."
            maxWidth="7xl"
            header={
                <LibraryPageHero
                    title={
                        <span>
                            Cek{' '}
                            <span className="bg-linear-to-r from-primary to-primary/75 bg-clip-text text-transparent">
                                Judul Skripsi
                            </span>
                        </span>
                    }
                    description="Pemeriksaan awal tingkat kemiripan judul skripsi Anda dengan koleksi repositori untuk meminimalkan penolakan proposal."
                />
            }
        >
            <div className="mx-auto max-w-4xl space-y-8">
                {/* Form Input Card */}
                <Card className="relative overflow-hidden border-border/60 bg-card/95 shadow-md backdrop-blur-xs transition-all duration-300 hover:shadow-lg">
                    <div className="absolute top-0 left-0 h-1 w-full bg-linear-to-r from-primary via-primary/85 to-primary/60" />

                    <CardContent className="p-6 sm:p-8">
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="space-y-4">
                                <div className="flex items-center justify-between">
                                    <Label
                                        htmlFor="judul"
                                        className="flex items-center gap-1.5 text-sm font-semibold text-foreground"
                                    >
                                        <BookOpen className="size-4 text-primary" />
                                        Draf Judul Skripsi
                                    </Label>
                                    {title && (
                                        <button
                                            type="button"
                                            onClick={() => setTitle('')}
                                            className="flex items-center gap-1 text-xs text-muted-foreground transition-colors hover:text-destructive"
                                            disabled={loading}
                                        >
                                            <Trash2 className="size-3" />
                                            Bersihkan
                                        </button>
                                    )}
                                </div>

                                <div className="relative flex flex-col gap-3 sm:flex-row sm:items-stretch">
                                    <div className="relative flex-1">
                                        <Input
                                            id="judul"
                                            placeholder="Contoh: Klasifikasi Sentimen Ulasan Kuliah Menggunakan Algoritma..."
                                            value={title}
                                            onChange={(e) =>
                                                setTitle(e.target.value)
                                            }
                                            className="min-h-12 border-border/70 bg-transparent pr-10 text-sm shadow-none transition-all focus-visible:border-primary focus-visible:ring-primary sm:text-base"
                                            disabled={loading}
                                            autoComplete="off"
                                        />
                                        <Search className="pointer-events-none absolute top-1/2 right-3.5 size-4 -translate-y-1/2 text-muted-foreground/50" />
                                    </div>
                                    <Button
                                        id="btn-cek-kemiripan"
                                        type="submit"
                                        disabled={loading || !title.trim()}
                                        className="flex h-12 w-full cursor-pointer items-center justify-center gap-2 px-6 font-semibold shadow-xs transition-transform active:scale-[0.98] sm:w-auto"
                                    >
                                        {loading ? (
                                            <Loader2 className="size-4 animate-spin" />
                                        ) : (
                                            <Sparkles className="size-4" />
                                        )}
                                        {loading
                                            ? 'Menganalisis...'
                                            : 'Cek Kemiripan'}
                                    </Button>
                                </div>

                                {/* Live Quality Validators */}
                                <div className="flex flex-wrap items-center gap-4 rounded-xl border border-border/50 bg-muted/20 px-3 py-2 text-xs">
                                    <span className="font-medium text-muted-foreground">
                                        Kriteria:
                                    </span>
                                    <div className="flex items-center gap-1.5">
                                        <span
                                            className={`inline-flex size-4 items-center justify-center rounded-full text-[10px] ${isWordCountValid ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-muted text-muted-foreground'}`}
                                        >
                                            {isWordCountValid ? (
                                                <Check className="size-3" />
                                            ) : (
                                                '1'
                                            )}
                                        </span>
                                        <span
                                            className={
                                                isWordCountValid
                                                    ? 'font-medium text-emerald-600 dark:text-emerald-400'
                                                    : 'text-muted-foreground'
                                            }
                                        >
                                            Min. 5 kata ({wordCount})
                                        </span>
                                    </div>
                                    <div className="flex items-center gap-1.5">
                                        <span
                                            className={`inline-flex size-4 items-center justify-center rounded-full text-[10px] ${isCharCountValid ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-muted text-muted-foreground'}`}
                                        >
                                            {isCharCountValid ? (
                                                <Check className="size-3" />
                                            ) : (
                                                '2'
                                            )}
                                        </span>
                                        <span
                                            className={
                                                isCharCountValid
                                                    ? 'font-medium text-emerald-600 dark:text-emerald-400'
                                                    : 'text-muted-foreground'
                                            }
                                        >
                                            Min. 5 karakter ({charCount})
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {/* Clickable Examples */}
                            <div className="space-y-2">
                                <span className="flex items-center gap-1 text-xs font-semibold text-muted-foreground">
                                    <Info className="size-3.5 text-primary/70" />
                                    Atau klik contoh judul berikut untuk
                                    mencoba:
                                </span>
                                <div className="grid gap-2 sm:grid-cols-2">
                                    {EXAMPLES.map((example, idx) => (
                                        <button
                                            key={idx}
                                            type="button"
                                            onClick={() =>
                                                handleExampleClick(example)
                                            }
                                            className="line-clamp-2 cursor-pointer rounded-lg border border-border/40 bg-muted/5 p-2.5 text-left text-xs font-medium text-muted-foreground transition-all hover:border-primary/30 hover:bg-primary/5 hover:text-foreground"
                                            disabled={loading}
                                        >
                                            {example}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {turnstileEnabled && turnstileSiteKey && (
                                <div className="flex justify-center rounded-2xl border border-dashed border-border/70 bg-muted/10 px-4 py-5">
                                    <Turnstile
                                        ref={turnstileRef}
                                        siteKey={turnstileSiteKey}
                                        onSuccess={(token) =>
                                            setTurnstileToken(token)
                                        }
                                        onExpire={() => setTurnstileToken(null)}
                                        onError={() => setTurnstileToken(null)}
                                    />
                                </div>
                            )}

                            {error ? (
                                <div className="animate-shake flex items-start gap-2 rounded-xl border border-destructive/20 bg-destructive/5 px-4 py-3 text-sm text-destructive">
                                    <ShieldAlert className="mt-0.5 size-4 shrink-0" />
                                    <span>{error}</span>
                                </div>
                            ) : null}
                        </form>
                    </CardContent>
                </Card>

                {/* Loading / Scanning Simulator */}
                {loading && (
                    <Card className="animate-in overflow-hidden border-primary/20 bg-linear-to-b from-primary/5 to-transparent shadow-md duration-300 fade-in slide-in-from-bottom-4">
                        <CardContent className="flex flex-col items-center space-y-6 p-8 text-center">
                            {/* Scanning radar visual effect */}
                            <div className="relative flex size-24 items-center justify-center">
                                <div className="absolute inset-0 animate-ping rounded-full bg-primary/10" />
                                <div className="absolute inset-2 animate-pulse rounded-full bg-primary/20" />
                                <div className="relative flex size-16 items-center justify-center rounded-full border border-primary/30 bg-primary/10 text-primary">
                                    <Loader2 className="size-8 animate-spin" />
                                </div>
                            </div>

                            <div className="space-y-2">
                                <h3 className="flex items-center justify-center gap-2 text-lg font-bold text-foreground">
                                    Menganalisis Judul Skripsi...
                                </h3>
                                <p className="mx-auto max-w-md text-sm text-muted-foreground">
                                    Sistem sedang memproses draf judul Anda dan
                                    membandingkannya dengan ribuan skripsi yang
                                    terindeks.
                                </p>
                            </div>

                            {/* Step list showing scanning phases */}
                            <div className="w-full max-w-sm space-y-3.5 rounded-xl border border-border/50 bg-background/50 p-4 text-left">
                                {SCANNING_STEPS.map((step, idx) => {
                                    const isDone = scanStep > idx;
                                    const isActive = scanStep === idx;

                                    return (
                                        <div
                                            key={idx}
                                            className="flex items-center gap-3 transition-all duration-300"
                                        >
                                            {isDone ? (
                                                <span className="flex size-5 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                                    <Check className="size-3.5 stroke-[3px]" />
                                                </span>
                                            ) : isActive ? (
                                                <span className="flex size-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                                    <Loader2 className="size-3 animate-spin" />
                                                </span>
                                            ) : (
                                                <span className="flex size-5 shrink-0 items-center justify-center rounded-full border border-border text-[10px] font-semibold text-muted-foreground">
                                                    {idx + 1}
                                                </span>
                                            )}
                                            <span
                                                className={`text-xs ${isDone ? 'text-muted-foreground line-through decoration-muted-foreground/30' : isActive ? 'font-semibold text-primary' : 'text-muted-foreground/60'}`}
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
                {result && !loading ? (
                    <div ref={resultsRef} className="scroll-mt-24 space-y-4">
                        <div className="flex items-center justify-between px-1">
                            <h2 className="flex items-center gap-2 text-lg font-bold text-foreground">
                                <Sparkles className="size-4 text-primary" />
                                Hasil Analisis Kemiripan
                            </h2>
                            <span className="text-xs text-muted-foreground">
                                Pencarian selesai
                            </span>
                        </div>
                        <Separator className="bg-border/60" />
                        <SimilarityResultsSection
                            result={result}
                            userTitle={title}
                        />
                    </div>
                ) : null}

                {/* Initial Info state */}
                {!result && !loading ? (
                    <Card className="overflow-hidden border-border/60 bg-card/90 shadow-sm">
                        <CardHeader className="border-b border-border/50 bg-muted/10 pb-4">
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <BookOpen className="size-4.5 text-primary" />
                                Alur Pemeriksaan Judul
                            </CardTitle>
                            <CardDescription>
                                Ketahui bagaimana sistem menguji orisinalitas
                                judul skripsi Anda.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="pt-6">
                            <SimilarityHowItWorks />
                        </CardContent>
                    </Card>
                ) : null}
            </div>
        </PageLayout>
    );
}
