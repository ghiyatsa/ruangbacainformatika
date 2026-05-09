import { Turnstile } from '@marsidev/react-turnstile';
import type { TurnstileInstance } from '@marsidev/react-turnstile';
import { AlertCircle, BookOpen, Loader2, Search } from 'lucide-react';
import type { FormEvent } from 'react';
import { useRef, useState } from 'react';
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
    const resultsRef = useRef<HTMLDivElement>(null);
    const turnstileRef = useRef<TurnstileInstance | null>(null);

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();

        const words = title
            .trim()
            .split(/\s+/)
            .filter((word) => word.length > 0);

        if (!title || title.trim().length < 5) {
            setError('Judul harus terdiri dari minimal 5 karakter.');

            return;
        }

        if (words.length < 5) {
            setError('Masukkan minimal 3 kata agar hasil lebih akurat.');

            return;
        }

        if (turnstileEnabled && !turnstileToken) {
            setError('Silakan selesaikan verifikasi keamanan.');

            return;
        }

        setLoading(true);
        setError(null);
        setResult(null);

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

    return (
        <PageLayout
            title="Cek Kemiripan Judul"
            maxWidth="7xl"
            header={
                <LibraryPageHero
                    badge={
                        <>
                            <BookOpen className="size-4 text-primary" />
                            Layanan Perpustakaan
                        </>
                    }
                    title={
                        <>
                            Cek Kemiripan{' '}
                            <span className="bg-linear-to-r from-primary to-primary/60 bg-clip-text text-transparent">
                                Judul Skripsi
                            </span>
                        </>
                    }
                    description="Verifikasi keaslian judul penelitian Anda sebelum mengajukan proposal. Sistem kami membandingkan secara semantik dengan seluruh koleksi perpustakaan."
                />
            }
        >
            <div className="mx-auto max-w-4xl space-y-8">
                {/* Search Card */}
                <Card className="border-border/60 bg-card/90 shadow-sm">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Search className="size-5 text-primary" />
                            Pemeriksaan Judul
                        </CardTitle>
                        <CardDescription>
                            Masukkan judul skripsi yang ingin Anda ajukan ke
                            perpustakaan.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="judul">Judul Skripsi</Label>
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
                                    Minimal 5 kata. Hasil diurutkan berdasarkan
                                    tingkat kemiripan.
                                </p>
                            </div>

                            {turnstileEnabled && turnstileSiteKey && (
                                <div className="flex justify-center">
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
                        <SimilarityResultsSection result={result} />
                    </div>
                )}

                {/* How it works — shown before first search */}
                {!result && !loading && (
                    <div className="space-y-4">
                        <p className="text-center text-sm font-medium text-muted-foreground">
                            Cara kerja
                        </p>
                        <SimilarityHowItWorks />
                    </div>
                )}
            </div>
        </PageLayout>
    );
}
