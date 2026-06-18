import { Link } from '@inertiajs/react';
import { ArrowRight, BookOpen, BookText, Search, Tags } from 'lucide-react';
import CountUp from '@/components/animated/CountUp';
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

export default function Hero({ stats, categoriesCount }: HeroProps) {
    const isMobile = useIsMobile();

    const openSearch = () => {
        window.dispatchEvent(new CustomEvent('open-global-search'));
    };

    const statsValues = {
        booksCount: stats.booksCount,
        availableItemsCount: stats.availableItemsCount,
        categoriesCount,
    };

    return (
        <section className="relative flex min-h-[calc(100svh-var(--header-height,7.5rem))] flex-col justify-center overflow-hidden py-8 sm:py-12 lg:py-16">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="grid gap-10 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-center lg:gap-14">
                    <div className="flex flex-col items-center gap-6 text-center lg:items-start lg:text-left">
                        <div className="flex max-w-3xl flex-col items-center gap-5 lg:items-start">
                            <div className="space-y-4 sm:space-y-5">
                                <p className="text-sm font-medium text-muted-foreground">
                                    Teknik Informatika Universitas Malikussaleh
                                </p>

                                <h1 className="mx-auto max-w-4xl text-4xl font-medium tracking-[-0.03em] text-balance sm:text-5xl lg:mx-0 lg:max-w-3xl lg:text-6xl xl:text-[4.5rem]">
                                    Cari <i>buku</i>, temukan referensi, pinjam
                                    tanpa ribet.
                                </h1>

                                <p className="mx-auto max-w-2xl text-base leading-7 text-muted-foreground sm:text-lg lg:mx-0">
                                    {RUANG_BACA_DESCRIPTION}
                                </p>
                            </div>

                            <div className="flex w-full flex-col items-stretch gap-3 sm:flex-row sm:items-stretch sm:justify-center lg:justify-start">
                                <button
                                    onClick={openSearch}
                                    className="group relative w-full transition-all duration-200 sm:max-w-sm sm:self-stretch"
                                    aria-label="Cari buku"
                                >
                                    <div className="flex h-full w-full items-center rounded-2xl border border-border bg-background px-4 py-3.5 transition-colors duration-200 group-hover:border-primary/30">
                                        <div className="flex w-full items-center justify-between gap-3 text-muted-foreground">
                                            <Search className="size-4 shrink-0 transition-colors group-hover:text-primary" />
                                            <span className="flex-1 text-left text-sm font-normal">
                                                Telusuri judul, penulis, atau
                                                subjek
                                            </span>
                                            <div className="flex items-center gap-1.5">
                                                <Kbd>Ctrl K</Kbd>
                                                <ArrowRight className="size-3.5 shrink-0 opacity-40 transition-all group-hover:opacity-80" />
                                            </div>
                                        </div>
                                    </div>
                                </button>

                                <Link
                                    href={books.index.url()}
                                    prefetch
                                    className="group inline-flex min-h-[58px] shrink-0 items-center justify-center gap-2 rounded-2xl bg-primary px-7 py-3.5 text-sm font-medium text-primary-foreground shadow-xs transition-all duration-200 hover:bg-primary/90 sm:self-stretch"
                                >
                                    <BookOpen className="size-4" />
                                    Jelajahi Katalog
                                    <ArrowRight className="size-4" />
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div className="w-full pt-2 lg:max-w-sm lg:justify-self-end lg:pt-0">
                        <div className="mx-auto grid max-w-2xl grid-cols-1 divide-y divide-border/60 rounded-2xl border border-border bg-background p-2">
                            {STATS.map(({ key, label, suffix }) => (
                                <div
                                    key={key}
                                    className="flex items-center justify-between px-4 py-3.5"
                                >
                                    <div className="flex items-baseline gap-0.5">
                                        {isMobile ? (
                                            <span className="text-2xl font-semibold text-foreground tabular-nums sm:text-3xl">
                                                {statsValues[
                                                    key
                                                ].toLocaleString('id-ID')}
                                            </span>
                                        ) : (
                                            <CountUp
                                                to={statsValues[key]}
                                                duration={1.8}
                                                className="text-2xl font-semibold text-foreground tabular-nums sm:text-3xl"
                                            />
                                        )}
                                        {suffix ? (
                                            <span className="text-lg font-semibold text-primary">
                                                {suffix}
                                            </span>
                                        ) : null}
                                    </div>
                                    <span className="text-sm font-medium text-muted-foreground">
                                        {label}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
