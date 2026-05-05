import { Head } from '@inertiajs/react';
import axios from 'axios';
import {
    AlertCircle,
    BookOpen,
    CheckCircle2,
    FileSearch,
    Loader2,
    Search,
    ShieldCheck,
    Sparkles,
    TriangleAlert,
    User,
} from 'lucide-react';
import type { FormEvent } from 'react';
import { useRef, useState } from 'react';
import { Badge } from '@/components/ui/badge';
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
import Footer from '@/components/welcome/Footer';

// ─── Types ───────────────────────────────────────────────────────────────────

interface SimilarityItem {
    judul: string;
    penulis: string;
    similarity_persen: number;
    level: string;
}

interface SimilarityResult {
    total_found: number;
    results: SimilarityItem[];
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

function getLevelConfig(level: string) {
    switch (level) {
        case 'TINGGI':
            return {
                label: 'Tinggi',
                color: 'text-red-600 dark:text-red-400',
                bg: 'bg-red-500',
                badgeClass:
                    'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800',
                trackClass: 'bg-red-100 dark:bg-red-950/30',
                icon: TriangleAlert,
            };
        case 'SEDANG':
            return {
                label: 'Sedang',
                color: 'text-amber-600 dark:text-amber-400',
                bg: 'bg-amber-500',
                badgeClass:
                    'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800',
                trackClass: 'bg-amber-100 dark:bg-amber-950/30',
                icon: AlertCircle,
            };
        default:
            return {
                label: 'Rendah',
                color: 'text-emerald-600 dark:text-emerald-400',
                bg: 'bg-emerald-500',
                badgeClass:
                    'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
                trackClass: 'bg-emerald-100 dark:bg-emerald-950/30',
                icon: CheckCircle2,
            };
    }
}

// ─── Sub-components ───────────────────────────────────────────────────────────

function SimilarityBar({ persen, level }: { persen: number; level: string }) {
    const cfg = getLevelConfig(level);

    return (
        <div className="flex w-full items-center gap-3">
            <div
                className={`h-2 flex-1 overflow-hidden rounded-full ${cfg.trackClass}`}
            >
                <div
                    className={`h-full rounded-full transition-all duration-700 ease-out ${cfg.bg}`}
                    style={{ width: `${persen}%` }}
                />
            </div>
            <span
                className={`w-10 text-right text-sm font-bold tabular-nums ${cfg.color}`}
            >
                {persen}%
            </span>
        </div>
    );
}

function ResultCard({ item, index }: { item: SimilarityItem; index: number }) {
    const cfg = getLevelConfig(item.level);
    const LevelIcon = cfg.icon;

    return (
        <Card className="group overflow-hidden transition-all duration-200 hover:shadow-md">
            {/* Colored accent strip */}
            <div
                className={`h-1 w-full ${item.level === 'TINGGI' ? 'bg-red-500' : item.level === 'SEDANG' ? 'bg-amber-500' : 'bg-emerald-500'}`}
            />

            <div className="p-5">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    {/* Book info */}
                    <div className="min-w-0 flex-1 space-y-2">
                        <div className="flex items-start gap-2">
                            <span className="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-bold text-muted-foreground">
                                {index + 1}
                            </span>
                            <h3 className="text-sm leading-snug font-semibold">
                                {item.judul}
                            </h3>
                        </div>
                        <div className="flex items-center gap-1.5 pl-8 text-xs text-muted-foreground">
                            <User className="size-3 shrink-0" />
                            <span>{item.penulis || 'Tidak diketahui'}</span>
                        </div>
                    </div>

                    {/* Badge */}
                    <div className="flex shrink-0 items-center gap-2 pl-8 sm:pl-0">
                        <Badge
                            variant="outline"
                            className={`gap-1 text-xs ${cfg.badgeClass}`}
                        >
                            <LevelIcon className="size-3" />
                            {cfg.label}
                        </Badge>
                    </div>
                </div>

                {/* Progress bar */}
                <div className="mt-4 pl-8">
                    <SimilarityBar
                        persen={item.similarity_persen}
                        level={item.level}
                    />
                </div>
            </div>
        </Card>
    );
}

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

function ResultsSection({ result }: { result: SimilarityResult }) {
    const highCount = result.results.filter((r) => r.level === 'TINGGI').length;
    const medCount = result.results.filter((r) => r.level === 'SEDANG').length;

    return (
        <div className="animate-in space-y-5 duration-500 fade-in slide-in-from-bottom-4">
            {/* Summary banner */}
            <div className="flex flex-wrap items-center justify-between gap-3 rounded-xl border bg-card px-5 py-3.5 shadow-sm">
                <div className="flex items-center gap-2">
                    <AlertCircle className="size-5 text-amber-500" />
                    <span className="font-semibold">
                        Ditemukan{' '}
                        <span className="text-primary">
                            {result.total_found}
                        </span>{' '}
                        kemiripan
                    </span>
                </div>
                <div className="flex flex-wrap gap-2 text-xs">
                    {highCount > 0 && (
                        <Badge
                            variant="outline"
                            className="border-red-200 bg-red-100 text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-400"
                        >
                            {highCount} Tinggi
                        </Badge>
                    )}
                    {medCount > 0 && (
                        <Badge
                            variant="outline"
                            className="border-amber-200 bg-amber-100 text-amber-700 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-400"
                        >
                            {medCount} Sedang
                        </Badge>
                    )}
                    {result.total_found - highCount - medCount > 0 && (
                        <Badge
                            variant="outline"
                            className="border-emerald-200 bg-emerald-100 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400"
                        >
                            {result.total_found - highCount - medCount} Rendah
                        </Badge>
                    )}
                </div>
            </div>

            {/* Result cards */}
            <div className="grid gap-3">
                {result.results.map((item, index) => (
                    <ResultCard key={index} item={item} index={index} />
                ))}
            </div>
        </div>
    );
}

// ─── How It Works ─────────────────────────────────────────────────────────────

function HowItWorks() {
    const steps = [
        {
            icon: FileSearch,
            title: 'Masukkan Judul',
            desc: 'Ketikkan judul skripsi yang ingin Anda ajukan.',
        },
        {
            icon: Sparkles,
            title: 'Analisis Semantik',
            desc: 'Sistem membandingkan kemiripan secara semantik dengan database.',
        },
        {
            icon: ShieldCheck,
            title: 'Lihat Hasilnya',
            desc: 'Dapatkan laporan kemiripan beserta tingkat risikonya.',
        },
    ];

    return (
        <div className="grid gap-4 sm:grid-cols-3">
            {steps.map(({ icon: Icon, title, desc }, i) => (
                <div
                    key={i}
                    className="flex flex-col items-center gap-3 rounded-xl border bg-card p-5 text-center shadow-sm"
                >
                    <div className="flex size-10 items-center justify-center rounded-xl bg-primary/10">
                        <Icon className="size-5 text-primary" />
                    </div>
                    <div>
                        <p className="text-sm font-semibold">{title}</p>
                        <p className="mt-0.5 text-xs text-muted-foreground">
                            {desc}
                        </p>
                    </div>
                </div>
            ))}
        </div>
    );
}

// ─── Page ────────────────────────────────────────────────────────────────────

export default function Similarity() {
    const [title, setTitle] = useState('');
    const [loading, setLoading] = useState(false);
    const [result, setResult] = useState<SimilarityResult | null>(null);
    const [error, setError] = useState<string | null>(null);
    const resultsRef = useRef<HTMLDivElement>(null);

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();

        if (!title || title.trim().length < 5) {
            setError('Judul harus terdiri dari minimal 5 karakter.');

            return;
        }

        setLoading(true);
        setError(null);
        setResult(null);

        try {
            const response = await axios.post('/similarity/check', {
                judul: title,
            });
            setResult(response.data);

            // Scroll to results
            setTimeout(() => {
                resultsRef.current?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                });
            }, 100);
        } catch (err: any) {
            setError(
                err.response?.data?.message ||
                    'Terjadi kesalahan saat memeriksa kemiripan.',
            );
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen bg-background font-sans text-foreground selection:bg-primary/10 selection:text-primary">
            <Head title="Cek Kemiripan Judul — Ruang Baca" />

            {/* Dot-grid texture */}
            <div
                className="pointer-events-none fixed inset-0 z-0 opacity-[0.03] dark:opacity-[0.05]"
                style={{
                    backgroundImage:
                        'radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0)',
                    backgroundSize: '24px 24px',
                }}
            />

            <div className="relative z-10 flex min-h-screen flex-col">
                <main className="flex-1">
                    {/* ── Hero ─────────────────────────────────────────── */}
                    <section className="py-14 md:py-20">
                        <div className="mx-auto max-w-3xl px-6 text-center lg:px-8">
                            {/* Icon badge */}
                            <div className="mb-6 inline-flex items-center gap-2 rounded-full border bg-card px-4 py-1.5 text-sm font-medium text-muted-foreground shadow-sm">
                                <BookOpen className="size-4 text-primary" />
                                Layanan Perpustakaan
                            </div>

                            <h1 className="text-4xl font-extrabold tracking-tight lg:text-5xl">
                                Cek Kemiripan{' '}
                                <span className="bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent">
                                    Judul Skripsi
                                </span>
                            </h1>
                            <p className="mx-auto mt-4 max-w-xl text-lg text-muted-foreground">
                                Verifikasi keaslian judul penelitian Anda
                                sebelum mengajukan proposal. Sistem kami
                                membandingkan secara semantik dengan seluruh
                                koleksi perpustakaan.
                            </p>
                        </div>
                    </section>

                    {/* ── Main Content ──────────────────────────────────── */}
                    <section className="pb-20">
                        <div className="mx-auto max-w-3xl space-y-8 px-6 lg:px-8">
                            {/* Search card */}
                            <Card className="shadow-lg">
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Search className="size-5 text-primary" />
                                        Pemeriksaan Judul
                                    </CardTitle>
                                    <CardDescription>
                                        Masukkan judul skripsi yang ingin Anda
                                        ajukan ke perpustakaan.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <form
                                        onSubmit={handleSubmit}
                                        className="space-y-4"
                                    >
                                        <div className="space-y-2">
                                            <Label htmlFor="judul">
                                                Judul Skripsi
                                            </Label>
                                            <div className="flex flex-col gap-3 sm:flex-row">
                                                <Input
                                                    id="judul"
                                                    placeholder="Contoh: Implementasi Algoritma KNN pada Sistem Rekomendasi..."
                                                    value={title}
                                                    onChange={(e) =>
                                                        setTitle(e.target.value)
                                                    }
                                                    className="flex-1"
                                                    disabled={loading}
                                                    autoComplete="off"
                                                />
                                                <Button
                                                    id="btn-cek-kemiripan"
                                                    type="submit"
                                                    disabled={loading}
                                                    className="w-full gap-2 sm:w-auto"
                                                >
                                                    {loading ? (
                                                        <Loader2 className="size-4 animate-spin" />
                                                    ) : (
                                                        <Search className="size-4" />
                                                    )}
                                                    {loading
                                                        ? 'Memeriksa...'
                                                        : 'Cek Sekarang'}
                                                </Button>
                                            </div>
                                            <p className="text-xs text-muted-foreground">
                                                Minimal 5 karakter. Hasil
                                                diurutkan berdasarkan tingkat
                                                kemiripan.
                                            </p>
                                        </div>

                                        {error && (
                                            <div className="flex items-center gap-2 rounded-lg border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">
                                                <AlertCircle className="size-4 shrink-0" />
                                                {error}
                                            </div>
                                        )}
                                    </form>
                                </CardContent>
                            </Card>

                            {/* Results */}
                            {result && (
                                <div ref={resultsRef} className="scroll-mt-24">
                                    <Separator className="my-2" />
                                    {result.total_found === 0 ? (
                                        <SafeResult />
                                    ) : (
                                        <ResultsSection result={result} />
                                    )}
                                </div>
                            )}

                            {/* How it works — only shown before first search */}
                            {!result && !loading && (
                                <div className="space-y-4">
                                    <p className="text-center text-sm font-medium text-muted-foreground">
                                        Cara kerja
                                    </p>
                                    <HowItWorks />
                                </div>
                            )}
                        </div>
                    </section>
                </main>

                <Footer />
            </div>
        </div>
    );
}
