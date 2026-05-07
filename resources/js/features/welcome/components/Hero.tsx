import { Link } from '@inertiajs/react';
import { ArrowRight, BookOpen, BookText, Search, Star } from 'lucide-react';
import type { Variants } from 'motion/react';
import { motion } from 'motion/react';
import { lazy, Suspense } from 'react';
import CountUp from '@/components/common/CountUp';
import ShinyText from '@/components/common/ShinyText';
import StarBorder from '@/components/common/StarBorder';
import type { WelcomeProps } from '@/features/welcome/types';
import books from '@/routes/books';

interface HeroProps {
    stats: WelcomeProps['stats'];
}

const Antigravity = lazy(() => import('@/components/common/Antigravity'));

const STATS = [
    {
        key: 'booksCount' as const,
        label: 'Judul Buku',
        icon: BookText,
        suffix: '+',
    },
    {
        key: 'availableItemsCount' as const,
        label: 'Eksemplar Tersedia',
        icon: BookOpen,
        suffix: '+',
    },
    {
        key: 'featuredCount' as const,
        label: 'Rekomendasi Unggulan',
        icon: Star,
        suffix: '',
    },
];

const container: Variants = {
    hidden: {},
    show: {
        transition: {
            staggerChildren: 0.12,
            delayChildren: 0.05,
        },
    },
};

const item: Variants = {
    hidden: { opacity: 0, y: 22 },
    show: {
        opacity: 1,
        y: 0,
        transition: { duration: 0.5, ease: 'easeOut' },
    },
};

export default function Hero({ stats }: HeroProps) {
    const openSearch = () => {
        window.dispatchEvent(new CustomEvent('open-global-search'));
    };

    return (
        <section className="relative top-0 flex h-svh flex-col justify-center overflow-hidden pt-20 sm:pt-24">
            {/* Particle background */}
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

            {/* Multi-layer radial glows */}
            <div
                className="pointer-events-none absolute inset-0 -z-10"
                aria-hidden="true"
            >
                {/* Primary center glow */}
                <div className="absolute top-[40%] left-1/2 h-[600px] w-[900px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary/8 blur-[100px] dark:bg-primary/15" />
                {/* Secondary top-right accent */}
                <div className="absolute -top-20 right-0 h-[400px] w-[400px] rounded-full bg-indigo-400/10 blur-[80px] dark:bg-indigo-500/15" />
                {/* Tertiary bottom-left accent */}
                <div className="absolute bottom-0 left-0 h-[300px] w-[500px] rounded-full bg-primary/5 blur-[80px] dark:bg-primary/10" />
            </div>

            <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <motion.div
                    className="flex flex-col items-center gap-4 text-center sm:gap-6"
                    variants={container}
                    initial="hidden"
                    animate="show"
                >
                    {/* Badge */}
                    <motion.div variants={item}>
                        <StarBorder
                            as="div"
                            className="rounded-full"
                            contentClassName="rounded-full bg-muted/60 backdrop-blur-sm px-4 py-1.5"
                            color="var(--color-primary)"
                            speed="4s"
                        >
                            <div className="inline-flex items-center gap-2 text-sm font-medium">
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
                    </motion.div>

                    {/* Heading */}
                    <motion.h1
                        variants={item}
                        className="max-w-4xl font-heading text-4xl leading-[1.08] font-bold tracking-tight sm:text-5xl md:text-6xl lg:text-7xl"
                    >
                        Gerbang Pengetahuan{' '}
                        <span className="relative inline-block">
                            <span className="bg-linear-to-r from-primary via-indigo-500 to-violet-500 bg-clip-text text-transparent">
                                Informatika
                            </span>
                        </span>{' '}
                        Masa Depan.
                    </motion.h1>

                    {/* Description */}
                    <motion.p
                        variants={item}
                        className="max-w-lg text-base leading-relaxed text-muted-foreground sm:max-w-xl sm:text-lg"
                    >
                        Perpustakaan digital resmi Program Studi Teknik
                        Informatika Universitas Malikussaleh. Mendukung riset,
                        pembelajaran akademik, dan pengembangan literasi
                        teknologi mahasiswa.
                    </motion.p>

                    {/* CTAs */}
                    <motion.div
                        variants={item}
                        className="flex w-full flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-center"
                    >
                        {/* Search CTA */}
                        <button
                            onClick={openSearch}
                            className="group relative w-full transition-all duration-200 hover:scale-[1.015] sm:max-w-sm"
                            aria-label="Cari buku"
                        >
                            <StarBorder
                                as="div"
                                color="var(--color-primary)"
                                contentClassName="backdrop-blur-sm bg-background/60 px-4 py-3.5 rounded-2xl"
                                className="w-full rounded-2xl"
                            >
                                <div className="flex items-center gap-3 text-muted-foreground">
                                    <Search className="size-4 shrink-0 transition-colors group-hover:text-primary" />
                                    <span className="flex-1 text-left text-sm font-normal">
                                        Cari buku, penulis, subjek...
                                    </span>
                                    <div className="flex items-center gap-1.5">
                                        <kbd className="pointer-events-none hidden items-center gap-0.5 rounded border border-border bg-muted/70 px-1.5 font-mono text-[10px] font-medium sm:flex">
                                            <span className="text-[11px]">
                                                ⌘
                                            </span>
                                            K
                                        </kbd>
                                        <ArrowRight className="size-3.5 shrink-0 opacity-40 transition-all group-hover:translate-x-0.5 group-hover:opacity-80" />
                                    </div>
                                </div>
                            </StarBorder>
                        </button>

                        {/* Primary CTA — Katalog */}
                        <Link
                            href={books.index.url()}
                            prefetch
                            className="group inline-flex shrink-0 items-center justify-center gap-2 rounded-2xl bg-primary px-7 py-3.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/30 transition-all duration-200 hover:scale-[1.015] hover:bg-primary/90 hover:shadow-xl hover:shadow-primary/40"
                        >
                            <BookOpen className="size-4 transition-transform duration-200 group-hover:scale-110" />
                            Jelajahi Katalog
                            <ArrowRight className="size-4 transition-transform duration-200 group-hover:translate-x-0.5" />
                        </Link>
                    </motion.div>

                    {/* Stats */}
                    <motion.div variants={item} className="w-full pt-2">
                        <div className="mx-auto grid max-w-2xl grid-cols-3 divide-x divide-border/50 overflow-hidden rounded-2xl border border-border/50 bg-background/60 backdrop-blur-sm">
                            {STATS.map(({ key, label, icon: Icon, suffix }) => (
                                <div
                                    key={key}
                                    className="group flex flex-col items-center gap-2 px-4 py-5 transition-colors duration-200 hover:bg-primary/5 sm:px-8"
                                >
                                    <div className="flex size-9 items-center justify-center rounded-xl bg-primary/10 transition-colors group-hover:bg-primary/15">
                                        <Icon className="size-4 text-primary" />
                                    </div>
                                    <div className="flex items-baseline gap-0.5">
                                        <CountUp
                                            to={stats[key]}
                                            duration={1.8}
                                            className="text-2xl font-bold text-foreground tabular-nums sm:text-3xl"
                                        />
                                        {suffix && (
                                            <span className="text-lg font-bold text-primary">
                                                {suffix}
                                            </span>
                                        )}
                                    </div>
                                    <span className="text-center text-[11px] leading-tight font-medium text-muted-foreground sm:text-xs">
                                        {label}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </motion.div>
                </motion.div>
            </div>
        </section>
    );
}
