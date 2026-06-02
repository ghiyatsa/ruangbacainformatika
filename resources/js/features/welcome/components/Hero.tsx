import { Link, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    Bell,
    BookOpen,
    BookText,
    Search,
    Tags,
} from 'lucide-react';
import CountUp from '@/components/common/CountUp';
import ShinyText from '@/components/common/ShinyText';
import StarBorder from '@/components/common/StarBorder';
import { Kbd } from '@/components/ui/kbd';
import { useIsMobile } from '@/hooks/use-mobile';
import { RUANG_BACA_DESCRIPTION } from '@/lib/brand';
import books from '@/routes/books';
import type { WelcomeProps } from '@/features/welcome/types';

interface HeroProps {
    stats: WelcomeProps['stats'];
    categoriesCount: number;
}

const STATS = [
    {
        key: 'booksCount' as const,
        label: 'Judul',
        icon: BookText,
        suffix: '+',
    },
    {
        key: 'availableItemsCount' as const,
        label: 'Eksemplar',
        icon: BookOpen,
        suffix: '+',
    },
    {
        key: 'categoriesCount' as const,
        label: 'Kategori',
        icon: Tags,
        suffix: '+',
    },
];

const NOTICE_STYLES = {
    info: {
        icon: 'text-primary',
        ping: 'bg-primary/60',
        dot: 'bg-primary',
        link: 'text-primary hover:text-primary/80',
        shineColor: 'var(--color-primary)',
    },
    warning: {
        icon: 'text-amber-600 dark:text-amber-400',
        ping: 'bg-amber-500/60 dark:bg-amber-400/60',
        dot: 'bg-amber-500 dark:bg-amber-400',
        link: 'text-amber-700 hover:text-amber-600 dark:text-amber-400 dark:hover:text-amber-300',
        shineColor: '#f59e0b',
    },
    success: {
        icon: 'text-emerald-600 dark:text-emerald-400',
        ping: 'bg-emerald-500/60 dark:bg-emerald-400/60',
        dot: 'bg-emerald-500 dark:bg-emerald-400',
        link: 'text-emerald-700 hover:text-emerald-600 dark:text-emerald-400 dark:hover:text-emerald-300',
        shineColor: '#10b981',
    },
} as const;

export default function Hero({ stats, categoriesCount }: HeroProps) {
    const isMobile = useIsMobile();
    const {
        props: {
            site: { notice },
        },
    } = usePage();

    const openSearch = () => {
        window.dispatchEvent(new CustomEvent('open-global-search'));
    };

    const statsValues = {
        booksCount: stats.booksCount,
        availableItemsCount: stats.availableItemsCount,
        categoriesCount,
    };
    const noticeStyle = NOTICE_STYLES[notice.tone];

    return (
        <section className="relative top-0 -mt-13 flex min-h-svh flex-col justify-center overflow-hidden pt-33 pb-12 sm:mt-0 sm:h-svh sm:pt-24 sm:pb-0">
            <div
                className="pointer-events-none absolute inset-0 -z-10"
                aria-hidden="true"
            >
                <div className="absolute top-[42%] left-1/2 hidden h-[600px] w-[900px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary/8 blur-[100px] dark:bg-primary/15 sm:block" />
                <div className="absolute -top-12 right-0 h-48 w-48 rounded-full bg-indigo-400/8 blur-[52px] dark:bg-indigo-500/12 sm:-top-20 sm:h-[400px] sm:w-[400px] sm:blur-[80px]" />
                <div className="absolute bottom-0 left-0 h-40 w-56 rounded-full bg-primary/5 blur-[44px] dark:bg-primary/8 sm:h-[300px] sm:w-[500px] sm:blur-[80px]" />
            </div>

            <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div className="flex flex-col gap-4 sm:gap-6 lg:grid lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-center lg:gap-10">
                    <div className="flex flex-col items-center gap-4 text-center sm:gap-6 lg:items-start lg:text-left">
                        <div className="flex flex-col items-center gap-3 lg:items-start">
                            {notice.isActive && (
                                <div className="mb-4 hidden w-full justify-center md:flex lg:justify-start">
                                    <div className="group flex w-full max-w-sm items-start justify-center gap-3 text-center text-sm text-foreground sm:max-w-md sm:flex-row sm:flex-wrap sm:items-center sm:justify-center sm:gap-x-2 sm:gap-y-1 lg:justify-start lg:text-left">
                                        <span
                                            className={`relative flex size-8 shrink-0 items-center justify-center transition-transform duration-200 group-hover:scale-105 ${noticeStyle.icon}`}
                                        >
                                            <Bell className="origin-top motion-safe:animate-(--animate-bell-swing)" />
                                            <span className="absolute top-1.5 right-1.5 flex size-2">
                                                <span
                                                    className={`absolute inline-flex h-full w-full animate-ping rounded-full ${noticeStyle.ping}`}
                                                />
                                                <span
                                                    className={`relative inline-flex size-2 rounded-full ${noticeStyle.dot}`}
                                                />
                                            </span>
                                        </span>

                                        <div className="min-w-0 flex-1 space-y-1 sm:flex sm:flex-wrap sm:items-center sm:justify-center sm:space-y-0 sm:gap-x-2 sm:gap-y-1 lg:justify-start">
                                            <span className="block min-w-0 font-medium text-pretty">
                                                <ShinyText
                                                    text={notice.text}
                                                    speed={2}
                                                    delay={0}
                                                    color="var(--color-foreground)"
                                                    shineColor={
                                                        noticeStyle.shineColor
                                                    }
                                                    spread={120}
                                                    direction="left"
                                                    yoyo={false}
                                                    pauseOnHover={false}
                                                    disabled={isMobile}
                                                />
                                            </span>

                                            {notice.url && (
                                                <a
                                                    href={notice.url}
                                                    className={`inline-flex shrink-0 justify-center font-semibold transition-colors ${noticeStyle.link}`}
                                                >
                                                    {notice.linkLabel ??
                                                        'Baca selengkapnya'}
                                                </a>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>

                        <h1 className="max-w-4xl font-heading text-5xl leading-[1.02] font-bold tracking-tight text-balance sm:text-5xl md:text-6xl lg:max-w-3xl lg:text-7xl">
                            Ruang Baca{' '}
                            <span className="relative inline-block">
                                <span className="bg-linear-to-r from-primary via-indigo-500 to-violet-500 bg-clip-text text-transparent">
                                    Informatika
                                </span>
                            </span>
                        </h1>

                        <p className="max-w-lg text-base leading-relaxed text-muted-foreground sm:max-w-xl sm:text-lg lg:max-w-2xl">
                            {RUANG_BACA_DESCRIPTION}
                        </p>

                        <div className="flex w-full flex-col items-stretch gap-3 sm:flex-row sm:items-stretch sm:justify-center lg:justify-start">
                            <button
                                onClick={openSearch}
                                className="group relative w-full transition-all duration-200 hover:scale-[1.015] sm:max-w-sm sm:self-stretch"
                                aria-label="Cari buku"
                            >
                                <StarBorder
                                    as="div"
                                    color="var(--color-primary)"
                                    contentClassName="flex h-full w-full items-center rounded-2xl bg-background px-4 py-3.5"
                                    className="h-full w-full rounded-2xl"
                                >
                                    <div className="flex w-full items-center justify-between gap-3 text-muted-foreground">
                                        <Search className="size-4 shrink-0 transition-colors group-hover:text-primary" />
                                        <span className="flex-1 text-left text-sm font-normal">
                                            Telusuri judul, penulis, atau subjek
                                        </span>
                                        <div className="flex items-center gap-1.5">
                                            <Kbd>Ctrl K</Kbd>
                                            <ArrowRight className="size-3.5 shrink-0 opacity-40 transition-all group-hover:translate-x-0.5 group-hover:opacity-80" />
                                        </div>
                                    </div>
                                </StarBorder>
                            </button>

                            <Link
                                href={books.index.url()}
                                prefetch
                                className="group inline-flex min-h-[58px] shrink-0 items-center justify-center gap-2 rounded-2xl bg-primary px-7 py-3.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/30 transition-all duration-200 hover:scale-[1.015] hover:bg-primary/90 hover:shadow-xl hover:shadow-primary/40 sm:self-stretch"
                            >
                                <BookOpen className="size-4 transition-transform duration-200 group-hover:scale-110" />
                                Jelajahi Katalog
                                <ArrowRight className="size-4 transition-transform duration-200 group-hover:translate-x-0.5" />
                            </Link>
                        </div>
                    </div>

                    <div className="w-full pt-2 lg:max-w-sm lg:justify-self-end lg:pt-0">
                        <div className="mx-auto grid max-w-2xl grid-cols-3 divide-x divide-border/50 overflow-hidden rounded-2xl border border-border/50 bg-background lg:grid-cols-1 lg:divide-x-0 lg:divide-y">
                            {STATS.map(({ key, label, icon: Icon, suffix }) => (
                                <div
                                    key={key}
                                    className="group flex flex-col items-center gap-2 px-4 py-5 transition-colors duration-200 hover:bg-primary/5 sm:px-8 lg:flex-row lg:items-center lg:justify-between lg:px-6"
                                >
                                    <div className="flex flex-col items-center gap-2 lg:flex-row lg:items-center">
                                        <div className="flex size-9 items-center justify-center rounded-xl bg-primary/10 transition-colors group-hover:bg-primary/15">
                                            <Icon className="size-4 text-primary" />
                                        </div>
                                        <span className="text-center text-[11px] leading-tight font-medium text-muted-foreground sm:text-xs lg:text-left">
                                            {label}
                                        </span>
                                    </div>
                                    <div className="flex items-baseline gap-0.5">
                                        {isMobile ? (
                                            <span className="text-2xl font-bold text-foreground tabular-nums sm:text-3xl">
                                                {statsValues[key].toLocaleString(
                                                    'id-ID',
                                                )}
                                            </span>
                                        ) : (
                                            <CountUp
                                                to={statsValues[key]}
                                                duration={1.8}
                                                className="text-2xl font-bold text-foreground tabular-nums sm:text-3xl"
                                            />
                                        )}
                                        {suffix && (
                                            <span className="text-lg font-bold text-primary">
                                                {suffix}
                                            </span>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
