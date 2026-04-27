import { Link } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { lazy, Suspense } from 'react';
import CatalogController from '@/actions/App/Http/Controllers/CatalogController';
import CountUp from '@/components/CountUp';
import ShinyText from '@/components/ShinyText';
import StarBorder from '@/components/StarBorder';
import type { WelcomeProps } from './types';

interface HeroProps {
    stats: WelcomeProps['stats'];
}

const Antigravity = lazy(() => import('@/components/Antigravity'));

export default function Hero({ stats }: HeroProps) {
    return (
        <section className="relative overflow-hidden pt-16 pb-20 lg:pt-24 lg:pb-32">
            <div className="pointer-events-none absolute inset-0 -z-10 h-full w-full">
                <Suspense fallback={null}>
                    <Antigravity
                        count={300}
                        magnetRadius={10}
                        ringRadius={7}
                        waveSpeed={0.4}
                        waveAmplitude={1}
                        particleSize={1}
                        lerpSpeed={0.05}
                        color="#2900cd"
                        autoAnimate
                        particleVariance={1}
                        rotationSpeed={0}
                        depthFactor={1}
                        pulseSpeed={3}
                        particleShape="capsule"
                        fieldStrength={10}
                    />
                </Suspense>
            </div>

            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="flex flex-col items-center gap-8 text-center">
                    <StarBorder
                        as="div"
                        className="rounded-full"
                        contentClassName="rounded-full bg-muted/50 backdrop-blur-sm px-3 py-1"
                        color="var(--color-primary)"
                        speed="4s"
                    >
                        <div className="animate-fade-in inline-flex items-center gap-2 text-sm font-medium">
                            <ShinyText
                                text="✨ Pusat Literasi Digital Teknik Informatika"
                                speed={2}
                                delay={0}
                                color="var(--color-foreground)"
                                shineColor="var(--color-primary)"
                                spread={120}
                                direction="left"
                                yoyo={false}
                                pauseOnHover={false}
                                disabled={false}
                            />
                        </div>
                    </StarBorder>

                    <h1 className="max-w-4xl font-heading text-4xl leading-[1.1] font-bold tracking-tight sm:text-6xl lg:text-7xl">
                        Gerbang Pengetahuan{' '}
                        <span className="bg-linear-to-r from-primary to-indigo-600 bg-clip-text text-transparent">
                            Informatika
                        </span>{' '}
                        Masa Depan.
                    </h1>

                    <p className="max-w-2xl text-lg leading-relaxed text-muted-foreground sm:text-xl">
                        Akses ratusan buku akademik, literatur teknologi
                        terbaru, dan karya ilmiah khusus bidang Teknik
                        Informatika Universitas Malikussaleh dalam satu platform
                        terintegrasi.
                    </p>

                    <div className="w-full max-w-xl">
                        <Link
                            href={CatalogController.url()}
                            className="block transition-transform"
                        >
                            <StarBorder
                                as="div"
                                color="var(--color-primary)"
                                contentClassName="backdrop-blur-sm bg-muted/50 px-4 py-3 rounded-2xl"
                                className="rounded-2xl"
                            >
                                <div className="flex items-center gap-3 text-muted-foreground">
                                    <Search className="size-5" />
                                    <span className="text-base font-normal">
                                        Cari judul buku, penulis, atau subjek...
                                    </span>
                                </div>
                            </StarBorder>
                        </Link>
                    </div>

                    <div className="w-full max-w-2xl">
                        <div className="mt-4 flex flex-wrap justify-center gap-x-20 gap-y-2 text-sm text-muted-foreground">
                            <div className="flex flex-col gap-2">
                                <CountUp
                                    to={stats.booksCount}
                                    duration={1.5}
                                    className="text-4xl font-bold text-foreground"
                                />
                                <span>Jumlah Buku</span>
                            </div>
                            <div className="flex flex-col gap-2">
                                <CountUp
                                    to={stats.availableItemsCount}
                                    duration={1.5}
                                    className="text-4xl font-bold text-foreground"
                                />
                                <span>Eksemplar Tersedia</span>
                            </div>
                            <div className="flex flex-col gap-2">
                                <CountUp
                                    to={stats.featuredCount}
                                    duration={1.5}
                                    className="text-4xl font-bold text-foreground"
                                />
                                <span>Rekomendasi Unggulan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
