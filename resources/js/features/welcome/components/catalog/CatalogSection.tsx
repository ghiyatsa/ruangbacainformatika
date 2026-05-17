import type { WelcomeProps } from '@/features/welcome/types';
import CatalogCtaGrid from './CatalogCtaGrid';
import FeaturedSpotlight from './FeaturedSpotlight';
import NewBooksPreview from './NewBooksPreview';
import PopularBooks from './PopularBooks';
import PopularCategories from './PopularCategories';
import SectionHeader from './SectionHeader';

interface CatalogSectionProps {
    stats: WelcomeProps['stats'];
    featuredBooks: WelcomeProps['featuredBooks'];
    popularBooks: WelcomeProps['popularBooks'];
    books: WelcomeProps['books'];
    categories: WelcomeProps['categories'];
}

export default function CatalogSection({
    stats,
    featuredBooks,
    popularBooks,
    books,
    categories,
}: CatalogSectionProps) {
    return (
        <section className="py-16 sm:py-20 lg:py-28">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="flex flex-col gap-12 lg:gap-16">
                    <div className="flex flex-col gap-6">
                        <SectionHeader
                            title="Buku Unggulan"
                            subtitle="Koleksi pilihan yang relevan untuk riset dan pembelajaran."
                        />

                        <FeaturedSpotlight featuredBooks={featuredBooks} />
                    </div>

                    <NewBooksPreview
                        books={books}
                        totalBooks={stats?.booksCount ?? 0}
                    />

                    <PopularCategories categories={categories} />

                    <PopularBooks popularBooks={popularBooks} />

                    <CatalogCtaGrid />
                </div>
            </div>
        </section>
    );
}
