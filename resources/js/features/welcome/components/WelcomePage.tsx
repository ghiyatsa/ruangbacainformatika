import { WhenVisible } from '@inertiajs/react';
import { SeoHead } from '@/components/common/SeoHead';
import { Skeleton } from '@/components/ui/skeleton';
import CatalogSection from '@/features/welcome/components/catalog/CatalogSection';
import CategoryMarquee from '@/features/welcome/components/CategoryMarquee';
import Hero from '@/features/welcome/components/Hero';
import type { WelcomeProps } from '@/features/welcome/types';

function CategoryMarqueeSkeleton() {
    return (
        <section className="py-6 sm:py-8">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {Array.from({ length: 6 }, (_, index) => (
                        <div
                            key={`category-marquee-skeleton-${index}`}
                            className="rounded-2xl border bg-background p-4 sm:p-6"
                        >
                            <Skeleton className="mb-4 size-12 rounded-xl" />
                            <Skeleton className="mb-2 h-5 w-2/3" />
                            <Skeleton className="h-4 w-full" />
                            <Skeleton className="mt-2 h-4 w-4/5" />
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}

export default function WelcomePage({
    stats,
    featuredBooks,
    popularBooks,
    books,
    categories,
    marqueeCategories,
}: WelcomeProps) {
    return (
        <>
            <SeoHead description="Daftar buku dan arsip akademik Ruang Baca Teknik Informatika Universitas Malikussaleh." />

            {/* Pattern Overlay */}
            <div
                className="pointer-events-none fixed inset-0 z-0 opacity-[0.03] dark:opacity-[0.05]"
                style={{
                    backgroundImage:
                        'radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0)',
                    backgroundSize: '24px 24px',
                }}
            />

            <div className="relative z-10">
                <Hero
                    stats={stats}
                    categoriesCount={stats.activeCategoriesCount}
                />

                <div className="hidden md:block">
                    <WhenVisible
                        data="marqueeCategories"
                        buffer={250}
                        fallback={<CategoryMarqueeSkeleton />}
                    >
                        <CategoryMarquee categories={marqueeCategories} />
                    </WhenVisible>
                </div>

                <CatalogSection
                    stats={stats}
                    featuredBooks={featuredBooks}
                    popularBooks={popularBooks}
                    books={books}
                    categories={categories}
                />
            </div>
        </>
    );
}
