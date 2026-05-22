import { SeoHead } from '@/components/common/SeoHead';
import CatalogSection from '@/features/welcome/components/catalog/CatalogSection';
import CategoryMarquee from '@/features/welcome/components/CategoryMarquee';
import Hero from '@/features/welcome/components/Hero';
import type { WelcomeProps } from '@/features/welcome/types';

export default function WelcomePage({
    stats,
    featuredBooks,
    popularBooks,
    books,
    categories,
}: WelcomeProps) {
    const activeCategoriesCount = categories.filter(
        (category) => category.booksCount > 0,
    ).length;

    return (
        <>
            <SeoHead
                title="Beranda"
                description="Jelajahi koleksi buku, skripsi, tesis, dan laporan KP Ruang Baca Teknik Informatika Universitas Malikussaleh."
            />

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
                <Hero stats={stats} categoriesCount={activeCategoriesCount} />

                <CategoryMarquee categories={categories} />

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
