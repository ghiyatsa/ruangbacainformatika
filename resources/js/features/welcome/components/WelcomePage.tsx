import { usePage } from '@inertiajs/react';
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
    latestPosts,
}: WelcomeProps) {
    const page = usePage<any>();
    const siteUrl = page.props.site?.url || '';
    const siteName = page.props.name || 'Ruang Baca Informatika';

    const websiteJsonLd = {
        '@context': 'https://schema.org',
        '@type': 'WebSite',
        'name': siteName,
        'url': siteUrl,
        'potentialAction': {
            '@type': 'SearchAction',
            'target': {
                '@type': 'EntryPoint',
                'urlTemplate': `${siteUrl}/search?q={search_term_string}`
            },
            'query-input': 'required name=search_term_string'
        }
    };

    return (
        <>
            <SeoHead description="Daftar buku dan arsip akademik Ruang Baca Teknik Informatika Universitas Malikussaleh." />
            
            <script type="application/ld+json">
                {JSON.stringify(websiteJsonLd)}
            </script>

            <div className="relative z-10">
                <Hero
                    stats={stats}
                    categoriesCount={stats.activeCategoriesCount}
                />

                <CatalogSection
                    stats={stats}
                    featuredBooks={featuredBooks}
                    popularBooks={popularBooks}
                    mostBorrowedBooks={mostBorrowedBooks}
                    books={books}
                    popularCategoryShelves={popularCategoryShelves}
                    latestPosts={latestPosts}
                />
            </div>
        </>
    );
}
