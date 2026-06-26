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

    interface SectionItem {
        id: string;
        content: React.ReactNode;
        isFullWidth?: boolean;
    }

    const items: SectionItem[] = [];

    if (hasFeaturedBooks) {
        items.push({
            id: 'featured',
            isFullWidth: true,
            content: (
                <div className="mx-auto max-w-7xl py-8 sm:py-10 lg:py-12">
                    <div className="flex flex-col gap-6 px-4 sm:px-6 lg:px-8">
                        <SectionHeader
                            title="Buku Unggulan"
                        />
                    </div>
                    <div className="mt-6">
                        <FeaturedSpotlight featuredBooks={featuredBooks} />
                    </div>
                </div>
            ),
        });
    }

    items.push({
        id: 'new-books',
        content: <NewBooksPreview books={books} />,
    });

    items.push({
        id: 'popular-books',
        content: <PopularBooks popularBooks={popularBooks} />,
    });

    items.push({
        id: 'categories',
        isFullWidth: true,
        content: (
            <PopularCategoryShelves
                popularCategoryShelves={popularCategoryShelves}
            />
        ),
    });

    if (mostBorrowedBooks === undefined || mostBorrowedBooks.length > 0) {
        items.push({
            id: 'most-borrowed',
            content: <MostBorrowedBooks mostBorrowedBooks={mostBorrowedBooks} />,
        });
    }

    if (latestPosts && latestPosts.length > 0) {
        items.push({
            id: 'latest-posts',
            content: (
                <div className="flex flex-col gap-8 sm:gap-10">
                    <SectionHeader
                        title="Artikel Terbaru"
                    />

                    <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        {latestPosts.map((post) => (
                            <BlogPostCard key={post.id} post={post} />
                        ))}
                    </div>

                    <div className="flex justify-center">
                        <Button asChild size="lg" className="rounded-xl px-8">
                            <Link href={blog.index.url()}>
                                Semua artikel
                            </Link>
                        </Button>
                    </div>
                </div>
            ),
        });
    }

    return (
        <div className="w-full">
            {/* Divider between Hero and Section 1 */}
            <div className="w-full border-y border-border/60">
                <div
                    className="mx-auto h-6 max-w-7xl px-4 sm:h-8 sm:px-6 lg:px-8"
                    style={{
                        backgroundImage:
                            'repeating-linear-gradient(-45deg, var(--color-border) 0, var(--color-border) 1px, transparent 1px, transparent 12px)',
                    }}
                />
            </div>
            {items.map((item, index) => (
                <div key={item.id} className="w-full">
                    {index > 0 && (
                        <div className="w-full border-y border-border/60">
                            <div
                                className="mx-auto h-6 max-w-7xl px-4 sm:h-8 sm:px-6 lg:px-8"
                                style={{
                                    backgroundImage:
                                        'repeating-linear-gradient(-45deg, var(--color-border) 0, var(--color-border) 1px, transparent 1px, transparent 12px)',
                                }}
                            />
                        </div>
                    )}
                    {item.isFullWidth ? (
                        item.content
                    ) : (
                        <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12">
                            {item.content}
                        </div>
                    )}
                </div>
            ))}
        </div>
    );
}
