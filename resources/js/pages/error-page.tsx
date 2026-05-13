import { Head, Link } from '@inertiajs/react';
import { AlertTriangle, ArrowLeft, Home, RotateCcw } from 'lucide-react';
import { BackgroundPattern } from '@/components/layouts/BackgroundPattern';
import { Button, buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { home } from '@/routes';

const errorContent: Record<number, { title: string; description: string }> = {
    403: {
        title: 'Akses ditolak',
        description:
            'Anda tidak memiliki izin untuk membuka halaman atau menjalankan aksi ini.',
    },
    404: {
        title: 'Halaman tidak ditemukan',
        description:
            'Halaman yang Anda cari tidak tersedia, sudah dipindahkan, atau URL-nya tidak tepat.',
    },
    419: {
        title: 'Sesi telah berakhir',
        description:
            'Sesi keamanan Anda sudah kedaluwarsa. Silakan muat ulang halaman lalu coba lagi.',
    },
    429: {
        title: 'Terlalu banyak permintaan',
        description:
            'Permintaan dikirim terlalu sering. Tunggu sebentar sebelum mencoba lagi.',
    },
    500: {
        title: 'Terjadi kesalahan server',
        description:
            'Aplikasi mengalami kendala internal. Tim kami dapat memeriksanya dari log server.',
    },
    503: {
        title: 'Layanan sementara tidak tersedia',
        description:
            'Layanan sedang dalam pemeliharaan atau belum siap merespons. Coba beberapa saat lagi.',
    },
};

export default function ErrorPage({ status }: { status: number }) {
    const content = errorContent[status] ?? {
        title: 'Terjadi kesalahan',
        description:
            'Permintaan Anda tidak dapat diproses saat ini. Silakan coba lagi beberapa saat lagi.',
    };

    return (
        <>
            <Head title={`${status} ${content.title}`} />

            <main className="relative flex min-h-screen items-center justify-center overflow-hidden bg-background px-6 py-10 text-foreground transition-colors duration-300">
                <BackgroundPattern />

                {/* Decorative background gradients */}
                <div className="absolute top-0 left-1/2 -z-10 h-full w-full -translate-x-1/2 opacity-30">
                    <div className="absolute top-0 left-1/4 h-96 w-96 rounded-full bg-primary/20 blur-[120px] dark:bg-primary/10" />
                    <div className="absolute right-1/4 bottom-0 h-96 w-96 rounded-full bg-cyan-500/10 blur-[120px] dark:bg-cyan-500/5" />
                </div>

                <div className="mx-auto flex w-full max-w-5xl items-center justify-center">
                    <div className="grid w-full gap-10 rounded-[2rem] border border-border/50 bg-card/85 p-8 shadow-[0_30px_80px_-35px_rgba(0,0,0,0.25)] backdrop-blur-xl md:grid-cols-[1.1fr_0.9fr] md:p-12 dark:bg-card/50 dark:shadow-[0_30px_80px_-35px_rgba(0,0,0,0.5)]">
                        <div className="flex flex-col justify-center space-y-8">
                            <div className="inline-flex w-fit items-center gap-2 rounded-full border border-primary/20 bg-primary/5 px-4 py-2 text-sm font-medium text-primary ring-1 ring-primary/10">
                                <AlertTriangle className="size-4" />
                                Notifikasi Sistem
                            </div>

                            <div className="space-y-4">
                                <p className="text-sm font-semibold tracking-[0.35em] text-muted-foreground uppercase">
                                    Status {status}
                                </p>
                                <h1 className="max-w-xl text-4xl font-bold tracking-tight text-foreground md:text-5xl lg:text-6xl">
                                    {content.title}
                                </h1>
                                <p className="max-w-2xl text-base leading-relaxed text-muted-foreground md:text-lg">
                                    {content.description}
                                </p>
                            </div>

                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <Link
                                    href={home()}
                                    className={cn(
                                        buttonVariants({ size: 'lg' }),
                                        'gap-2 shadow-sm',
                                    )}
                                >
                                    <Home className="size-4" />
                                    Kembali ke beranda
                                </Link>

                                <Button
                                    type="button"
                                    variant="outline"
                                    size="lg"
                                    className="gap-2 bg-background/50 backdrop-blur-sm"
                                    onClick={() => window.location.reload()}
                                >
                                    <RotateCcw className="size-4" />
                                    Coba lagi
                                </Button>

                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="lg"
                                    className="gap-2"
                                    onClick={() => window.history.back()}
                                >
                                    <ArrowLeft className="size-4" />
                                    Kembali
                                </Button>
                            </div>
                        </div>

                        <div className="relative flex items-center justify-center">
                            {/* Decorative background for the status number */}
                            <div className="absolute inset-0 scale-95 -rotate-3 rounded-[2rem] bg-linear-to-br from-primary/20 via-transparent to-cyan-500/20 opacity-50 blur-2xl" />

                            <div className="relative flex h-full min-h-72 w-full items-center justify-center rounded-[1.75rem] border border-border/50 bg-background/50 p-8 shadow-inner backdrop-blur-md">
                                <div className="text-center">
                                    <span className="bg-linear-to-b from-primary to-primary/60 bg-clip-text text-[6rem] leading-none font-black tracking-tighter text-transparent md:text-[8rem]">
                                        {status}
                                    </span>
                                    <p className="mt-4 text-xs font-bold tracking-[0.4em] text-muted-foreground/60 uppercase">
                                        Ruang Baca
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </>
    );
}
