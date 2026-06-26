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
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { SimilarityHowItWorks } from '@/features/similarity/components/SimilarityHowItWorks';
import { SimilarityResultsSection } from '@/features/similarity/components/SimilarityResultsSection';
import { getCsrfToken } from '@/lib/csrf';
import similarityRoute from '@/routes/similarity';
import type { TurnstileInstance } from '@marsidev/react-turnstile';
import type { SubmitEvent } from 'react';
import type { SimilarityResult } from '@/features/similarity/types';

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
            const csrfToken = getCsrfToken();
            const response = await fetch(similarityRoute.check.url(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                },
                body: JSON.stringify({
                    judul: title,
                    'cf-turnstile-response': turnstileToken,
                }),
            });

            const contentType = response.headers.get('content-type') ?? '';
            const data = contentType.includes('application/json')
                ? ((await response.json()) as
                      | SimilarityResult
                      | { message?: string })
                : null;

            if (!response.ok) {
                throw new Error(
                    data && 'message' in data && data.message
                        ? data.message
                        : response.status === 419
                          ? 'Sesi keamanan sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.'
                          : response.status === 401
                            ? 'Sesi Anda tidak aktif. Silakan masuk kembali.'
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
        <>
            <SeoHead
                title="Cek Kemiripan Judul"
                description="Periksa kemiripan judul skripsi sebelum diajukan."
            />
            <div className="relative z-10 flex flex-1 flex-col">
                <PublicPageHero
                    title="Cek Judul Skripsi"
                    description="Pemeriksaan awal untuk membantu meninjau kemiripan judul skripsi Anda."
                    contentClassName="max-w-7xl px-4 sm:px-6 lg:px-8"
                />

                {/* Hatched Divider after Hero */}
                <div className="w-full border-b border-border/60 bg-background">
                    <div
                        className="mx-auto h-6 max-w-7xl px-4 sm:h-8 sm:px-6 lg:px-8"
                        style={{
                            backgroundImage:
                                'repeating-linear-gradient(-45deg, var(--color-border) 0, var(--color-border) 1px, transparent 1px, transparent 12px)',
                        }}
                    />
                </div>

                <div className="w-full bg-background py-8 sm:py-12">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="space-y-8">
                            {/* Form Input Card */}
                            <Card className="relative overflow-hidden border border-border bg-card shadow-none">
                                <CardContent className="pt-6">
                                    <form onSubmit={handleSubmit} className="space-y-4">
                                        <div className="space-y-4">
                                            <div className="flex justify-end">
                                                {title && (
                                                    <button
                                                        type="button"
                                                        onClick={() => setTitle('')}
                                                        className="flex items-center gap-1 text-xs text-muted-foreground hover:text-destructive"
                                                        disabled={loading}
                                                    >
                                                        <Trash2 className="size-3" />
                                                        Bersihkan
                                                    </button>
                                                )}
                                            </div>

                                            <div className="flex flex-col gap-3">
                                                <div className="space-y-2">
                                                    <Label
                                                        htmlFor="judul"
                                                        className="sr-only"
                                                    >
                                                        Draf judul skripsi
                                                    </Label>
                                                    <Textarea
                                                        id="judul"
                                                        placeholder="Contoh: Klasifikasi Sentimen Ulasan Kuliah Menggunakan Algoritma..."
                                                        value={title}
                                                        onChange={(e) =>
                                                            setTitle(e.target.value)
                                                        }
                                                        className="min-h-32 resize-y border-border bg-transparent px-4 py-3 text-sm leading-6 shadow-none focus-visible:border-primary focus-visible:ring-primary sm:text-base"
                                                        disabled={loading}
                                                        autoComplete="off"
                                                    />
                                                </div>
                                                <Button
                                                    id="btn-cek-kemiripan"
                                                    type="submit"
                                                    disabled={loading || !title.trim()}
                                                    className="flex h-12 w-full cursor-pointer items-center justify-center gap-2 rounded-xl px-6 font-semibold sm:w-auto"
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
                                            <div className="flex flex-wrap items-center gap-4 rounded-lg border border-border bg-muted/5 px-3 py-2 text-xs">
                                                <span className="font-medium text-muted-foreground">
                                                    Kriteria:
                                                </span>
                                                <div className="flex items-center gap-1.5">
                                                    <span
                                                        className={`inline-flex size-4 items-center justify-center rounded-full text-[10px] ${isWordCountValid ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400' : 'bg-muted text-muted-foreground'}`}
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
                                                                ? 'font-medium text-emerald-700 dark:text-emerald-400'
                                                                : 'text-muted-foreground'
                                                        }
                                                    >
                                                        Min. 5 kata ({wordCount})
                                                    </span>
                                                </div>
                                                <div className="flex items-center gap-1.5">
                                                    <span
                                                        className={`inline-flex size-4 items-center justify-center rounded-full text-[10px] ${isCharCountValid ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400' : 'bg-muted text-muted-foreground'}`}
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
                                                                ? 'font-medium text-emerald-700 dark:text-emerald-400'
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
                                                Atau klik contoh judul berikut untuk mencoba:
                                            </span>
                                            <div className="grid gap-2 sm:grid-cols-2">
                                                {EXAMPLES.map((example, idx) => (
                                                    <button
                                                        key={idx}
                                                        type="button"
                                                        onClick={() =>
                                                            handleExampleClick(example)
                                                        }
                                                        className="line-clamp-2 cursor-pointer rounded-lg border border-border bg-muted/5 p-2.5 text-left text-xs font-medium text-muted-foreground hover:bg-muted/10 hover:text-foreground"
                                                        disabled={loading}
                                                    >
                                                        {example}
                                                    </button>
                                                ))}
                                            </div>
                                        </div>

                                        {turnstileEnabled && turnstileSiteKey && (
                                            <div className="flex justify-center px-1 py-2 sm:py-3">
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
                                            <div className="animate-shake flex items-start gap-2 rounded-lg border border-destructive/20 bg-destructive/5 px-4 py-3 text-sm text-destructive">
                                                <ShieldAlert className="mt-0.5 size-4 shrink-0" />
                                                <span>{error}</span>
                                            </div>
                                        ) : null}
                                    </form>
                                </CardContent>
                            </Card>

                            {/* Loading / Scanning Simulator */}
                            {loading && (
                                <Card className="animate-in overflow-hidden border border-border bg-card shadow-none duration-300 fade-in">
                                    <CardContent className="flex flex-col items-center space-y-6 p-8 text-center">
                                        {/* Loading spinner */}
                                        <div className="flex size-16 items-center justify-center rounded-lg border border-border bg-muted/5 text-primary">
                                            <Loader2 className="size-8 animate-spin text-muted-foreground" />
                                        </div>

                                        <div className="space-y-2">
                                            <h3 className="flex items-center justify-center gap-2 text-lg font-bold text-foreground">
                                                Menganalisis Judul Skripsi...
                                            </h3>
                                            <p className="mx-auto max-w-md text-sm text-muted-foreground">
                                                Sistem sedang memeriksa judul Anda dan membandingkannya dengan data skripsi yang tersedia.
                                            </p>
                                        </div>

                                        {/* Step list showing scanning phases */}
                                        <div className="w-full max-w-sm space-y-3.5 rounded-lg border border-border bg-muted/5 p-4 text-left">
                                            {SCANNING_STEPS.map((step, idx) => {
                                                const isDone = scanStep > idx;
                                                const isActive = scanStep === idx;

                                                return (
                                                    <div
                                                        key={idx}
                                                        className="flex items-center gap-3"
                                                    >
                                                        {isDone ? (
                                                            <span className="flex size-5 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                                                <Check className="size-3.5 stroke-[3px]" />
                                                            </span>
                                                        ) : isActive ? (
                                                            <span className="flex size-5 shrink-0 items-center justify-center rounded-full bg-muted text-muted-foreground">
                                                                <Loader2 className="size-3 animate-spin" />
                                                            </span>
                                                        ) : (
                                                            <span className="flex size-5 shrink-0 items-center justify-center rounded-full border border-border text-[10px] font-semibold text-muted-foreground">
                                                                {idx + 1}
                                                            </span>
                                                        )}
                                                        <span
                                                            className={`text-xs ${isDone ? 'text-muted-foreground line-through decoration-muted-foreground/30' : isActive ? 'font-semibold text-foreground' : 'text-muted-foreground/60'}`}
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
                        </div>
                    </div>
                </div>

                {/* Hatched Divider before How It Works */}
                {!result && !loading && (
                    <>
                        <div className="w-full border-y border-border/60 bg-background">
                            <div
                                className="mx-auto h-6 max-w-7xl px-4 sm:h-8 sm:px-6 lg:px-8"
                                style={{
                                    backgroundImage:
                                        'repeating-linear-gradient(-45deg, var(--color-border) 0, var(--color-border) 1px, transparent 1px, transparent 12px)',
                                }}
                            />
                        </div>

                        <div className="w-full bg-background py-8 sm:py-12">
                            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                                <Card className="overflow-hidden border border-border bg-card shadow-none">
                                    <CardHeader className="border-b border-border/60 bg-muted/5">
                                        <CardTitle className="flex items-center gap-2 text-lg">
                                            Alur Pemeriksaan Judul
                                        </CardTitle>
                                        <CardDescription>
                                            Lihat cara sistem memeriksa judul skripsi Anda.
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="pt-6">
                                        <SimilarityHowItWorks />
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </>
                )}
            </div>
        </>
    );
}
