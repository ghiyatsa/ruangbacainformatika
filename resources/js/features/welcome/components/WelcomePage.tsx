import { SeoHead } from '@/components/common/SeoHead';
import CatalogSection from '@/features/welcome/components/CatalogSection';
import Hero from '@/features/welcome/components/Hero';
import type { WelcomeProps } from '@/features/welcome/types';

export default function WelcomePage({
    stats,
    featuredBooks,
    popularBooks,
    mostBorrowedBooks,
    books,
    popularCategoryShelves,
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

                <CatalogSection
                    featuredBooks={featuredBooks}
                    popularBooks={popularBooks}
                    mostBorrowedBooks={mostBorrowedBooks}
                    books={books}
                    popularCategoryShelves={popularCategoryShelves}
                />
            </div>
        </>
    );
}
