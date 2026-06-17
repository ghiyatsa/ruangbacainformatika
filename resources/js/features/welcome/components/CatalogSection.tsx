import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { BlogPostCard } from '@/features/blog/components/BlogPostCard';
import blog from '@/routes/blog';
import FeaturedSpotlight from './FeaturedSpotlight';
import MostBorrowedBooks from './MostBorrowedBooks';
import NewBooksPreview from './NewBooksPreview';
import PopularBooks from './PopularBooks';
import PopularCategoryShelves from './PopularCategoryShelves';
import SectionHeader from './SectionHeader';
import type { WelcomeProps } from '@/features/welcome/types';

interface CatalogSectionProps {
    stats: WelcomeProps['stats'];
    featuredBooks: WelcomeProps['featuredBooks'];
    popularBooks: WelcomeProps['popularBooks'];
    mostBorrowedBooks: WelcomeProps['mostBorrowedBooks'];
    books: WelcomeProps['books'];
    popularCategoryShelves: WelcomeProps['popularCategoryShelves'];
    latestPosts: WelcomeProps['latestPosts'];
}

export default function CatalogSection({
    stats,
    featuredBooks,
    popularBooks,
    mostBorrowedBooks,
    books,
    popularCategoryShelves,
    latestPosts,
}: CatalogSectionProps) {
    const hasFeaturedBooks = stats.featuredCount > 0;

    return (
        <section className="py-16 sm:py-20 lg:py-28">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="flex flex-col gap-12 lg:gap-16">
                    {hasFeaturedBooks ? (
                        <div className="flex flex-col gap-6">
                            <SectionHeader
                                title="Buku Unggulan"
                                subtitle="Pilihan buku unggulan dari ruang baca."
                            />

                            <FeaturedSpotlight featuredBooks={featuredBooks} />
                        </div>
                    ) : null}

                    <NewBooksPreview books={books} />

                    <PopularBooks popularBooks={popularBooks} />

                    <PopularCategoryShelves
                        popularCategoryShelves={popularCategoryShelves}
                    />

                    <MostBorrowedBooks
                        mostBorrowedBooks={mostBorrowedBooks}
                    />

                    {latestPosts && latestPosts.length > 0 ? (
                        <div className="flex flex-col gap-6">
                            <SectionHeader
                                title="Artikel Terbaru"
                                subtitle="Pilihan artikel terbaru dari Ruang Baca."
                                action={
                                    <Button asChild variant="outline" className="rounded-full">
                                        <Link href={blog.index.url()}>
                                            Semua artikel
                                        </Link>
                                    </Button>
                                }
                            />

                            <div className="grid gap-5 lg:grid-cols-3">
                                {latestPosts.map((post) => (
                                    <BlogPostCard key={post.id} post={post} />
                                ))}
                            </div>
                        </div>
                    ) : null}
                </div>
            </div>
        </section>
    );
}
