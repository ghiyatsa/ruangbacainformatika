import { ArrowRight, Search } from 'lucide-react';
import { lazy, Suspense } from 'react';
import CountUp from '@/components/common/CountUp';
import ShinyText from '@/components/common/ShinyText';
import StarBorder from '@/components/common/StarBorder';
import type { WelcomeProps } from './types';

interface HeroProps {
    stats: WelcomeProps['stats'];
}

const Antigravity = lazy(() => import('@/components/Antigravity'));

export default function Hero({ stats }: HeroProps) {
    return (
        <section className="relative overflow-hidden pt-20 pb-24 sm:pt-28 sm:pb-32 lg:pt-36 lg:pb-40">
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

            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="flex flex-col items-center gap-6 text-center sm:gap-8">
                    {/* Badge */}
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

                    {/* Heading */}
                    <h1 className="max-w-4xl font-heading text-4xl leading-[1.1] font-bold tracking-tight sm:text-5xl md:text-6xl lg:text-7xl">
                        Gerbang Pengetahuan{' '}
                        <span className="bg-linear-to-r from-primary to-indigo-600 bg-clip-text text-transparent">
                            Informatika
                        </span>{' '}
                        Masa Depan.
                    </h1>

                    {/* Description */}
                    <p className="max-w-xl px-2 text-base leading-relaxed text-muted-foreground sm:max-w-2xl sm:px-0 sm:text-lg md:text-xl">
                        Akses ratusan buku akademik, literatur teknologi
                        terbaru, dan karya ilmiah khusus bidang Teknik
                        Informatika Universitas Malikussaleh
                    </p>

                    {/* Search CTA */}
                    <div className="flex w-max justify-center px-4 sm:px-0">
                        <button
                            onClick={(e) => {
                                e.preventDefault();
                                window.dispatchEvent(
                                    new CustomEvent('open-global-search'),
                                );
                            }}
                            className="relative w-full max-w-sm transition-transform hover:scale-[1.02] sm:max-w-xl"
                        >
                            <StarBorder
                                as="div"
                                color="var(--color-primary)"
                                contentClassName="backdrop-blur-sm bg-muted/50 px-4 py-3 rounded-2xl"
                                className="w-full rounded-2xl"
                            >
                                <div className="flex items-center gap-3 text-muted-foreground">
                                    <Search className="size-5 shrink-0" />
                                    <span className="text-sm font-normal sm:text-base">
                                        Cari judul buku, penulis, atau subjek...
                                    </span>
                                    <div className="ml-auto flex items-center gap-2">
                                        <kbd className="pointer-events-none hidden items-center gap-1 rounded border bg-muted/50 px-1.5 font-mono text-[10px] font-medium opacity-70 sm:flex">
                                            <span className="text-xs">⌘</span>K
                                        </kbd>
                                        <ArrowRight className="size-4 shrink-0 opacity-50" />
                                    </div>
                                </div>
                            </StarBorder>
                        </button>
                    </div>

                    {/* Stats */}
                    <div className="mt-2 w-full max-w-2xl">
                        <div className="grid grid-cols-3 gap-4 text-sm text-muted-foreground sm:flex sm:flex-wrap sm:justify-center sm:gap-x-16 sm:gap-y-4">
                            <div className="flex flex-col items-center gap-1 sm:gap-2">
                                <CountUp
                                    to={stats.booksCount}
                                    duration={1.5}
                                    className="text-3xl font-bold text-foreground sm:text-4xl"
                                />
                                <span className="text-xs sm:text-sm">
                                    Judul Buku
                                </span>
                            </div>
                            <div className="flex flex-col items-center gap-1 sm:gap-2">
                                <CountUp
                                    to={stats.availableItemsCount}
                                    duration={1.5}
                                    className="text-3xl font-bold text-foreground sm:text-4xl"
                                />
                                <span className="text-xs sm:text-sm">
                                    Eksemplar Tersedia
                                </span>
                            </div>
                            <div className="flex flex-col items-center gap-1 sm:gap-2">
                                <CountUp
                                    to={stats.featuredCount}
                                    duration={1.5}
                                    className="text-3xl font-bold text-foreground sm:text-4xl"
                                />
                                <span className="text-xs sm:text-sm">
                                    Rekomendasi Unggulan
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
