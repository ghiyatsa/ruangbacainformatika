import { Head } from '@inertiajs/react';
import axios from 'axios';
import { AlertCircle, CheckCircle2, Search, Loader2 } from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
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
import Footer from '@/components/welcome/Footer';

interface SimilarityResult {
    total_found: number;
    results: Array<{
        judul: string;
        penulis: string;
        similarity_persen: number;
        level: string;
    }>;
}

export default function Similarity() {
    const [title, setTitle] = useState('');
    const [loading, setLoading] = useState(false);
    const [result, setResult] = useState<SimilarityResult | null>(null);
    const [error, setError] = useState<string | null>(null);

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
            <Head title="Cek Kemiripan Judul Skripsi" />

            {/* Pattern Overlay */}
            <div
                className="pointer-events-none fixed inset-0 z-0 opacity-[0.03] dark:opacity-[0.05]"
                style={{
                    backgroundImage:
                        'radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0)',
                    backgroundSize: '24px 24px',
                }}
            />

            <div className="relative z-10 flex min-h-screen flex-col">
                <main className="flex flex-1 flex-col items-center justify-center p-6 md:p-12">
                    <div className="w-full max-w-3xl space-y-8">
                        <div className="space-y-4 text-center">
                            <h1 className="text-4xl font-extrabold tracking-tight lg:text-5xl">
                                Cek Kemiripan Judul
                            </h1>
                            <p className="mx-auto max-w-2xl text-xl text-muted-foreground">
                                Verifikasi keaslian judul penelitian Anda
                                sebelum mengajukan proposal skripsi. Sistem akan
                                mencari kecocokan dengan database perpustakaan.
                            </p>
                        </div>

                        <Card className="shadow-lg">
                            <CardHeader>
                                <CardTitle>Pemeriksaan Cepat</CardTitle>
                                <CardDescription>
                                    Masukkan judul skripsi yang ingin Anda
                                    ajukan.
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
                                                placeholder="Contoh: Implementasi Algoritma KNN pada..."
                                                value={title}
                                                onChange={(e) =>
                                                    setTitle(e.target.value)
                                                }
                                                className="flex-1"
                                                disabled={loading}
                                            />
                                            <Button
                                                type="submit"
                                                disabled={loading}
                                                className="w-full sm:w-auto"
                                            >
                                                {loading ? (
                                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                ) : (
                                                    <Search className="mr-2 h-4 w-4" />
                                                )}
                                                Cek Sekarang
                                            </Button>
                                        </div>
                                    </div>
                                    {error && (
                                        <p className="text-sm font-medium text-destructive">
                                            {error}
                                        </p>
                                    )}
                                </form>
                            </CardContent>
                        </Card>

                        {result && (
                            <div className="animate-in space-y-6 duration-500 fade-in slide-in-from-bottom-4">
                                {result.total_found === 0 ? (
                                    <Card className="border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-950/20">
                                        <CardContent className="flex items-center gap-4 pt-6">
                                            <div className="rounded-full bg-green-100 p-3 dark:bg-green-900">
                                                <CheckCircle2 className="h-6 w-6 text-green-600 dark:text-green-400" />
                                            </div>
                                            <div>
                                                <h3 className="text-lg font-semibold text-green-900 dark:text-green-100">
                                                    Aman Digunakan
                                                </h3>
                                                <p className="text-green-700 dark:text-green-300">
                                                    Tidak ditemukan kemiripan
                                                    signifikan. Judul ini belum
                                                    pernah digunakan sebelumnya.
                                                </p>
                                            </div>
                                        </CardContent>
                                    </Card>
                                ) : (
                                    <div className="space-y-4">
                                        <div className="flex items-center gap-2">
                                            <AlertCircle className="h-5 w-5 text-amber-500" />
                                            <h2 className="text-xl font-bold">
                                                Ditemukan {result.total_found}{' '}
                                                Kemiripan
                                            </h2>
                                        </div>
                                        <div className="grid gap-4">
                                            {result.results.map(
                                                (item, index) => (
                                                    <Card
                                                        key={index}
                                                        className="overflow-hidden"
                                                    >
                                                        <div className="flex flex-col justify-between gap-4 p-6 sm:flex-row sm:items-center">
                                                            <div className="space-y-1">
                                                                <h3 className="leading-tight font-semibold">
                                                                    {item.judul}
                                                                </h3>
                                                                <p className="text-sm text-muted-foreground">
                                                                    {item.penulis ||
                                                                        'Tidak diketahui'}
                                                                </p>
                                                            </div>
                                                            <div className="flex shrink-0 items-center gap-3">
                                                                <div className="text-right">
                                                                    <div className="text-2xl font-bold">
                                                                        {
                                                                            item.similarity_persen
                                                                        }
                                                                        %
                                                                    </div>
                                                                    <div className="text-xs tracking-wider text-muted-foreground uppercase">
                                                                        Kemiripan
                                                                    </div>
                                                                </div>
                                                                <Badge
                                                                    variant={
                                                                        item.level ===
                                                                        'TINGGI'
                                                                            ? 'destructive'
                                                                            : item.level ===
                                                                                'SEDANG'
                                                                              ? 'secondary'
                                                                              : 'outline'
                                                                    }
                                                                    className={
                                                                        item.level ===
                                                                        'SEDANG'
                                                                            ? 'bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-300'
                                                                            : ''
                                                                    }
                                                                >
                                                                    {item.level}
                                                                </Badge>
                                                            </div>
                                                        </div>
                                                    </Card>
                                                ),
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </main>

                <Footer />
            </div>
        </div>
    );
}
