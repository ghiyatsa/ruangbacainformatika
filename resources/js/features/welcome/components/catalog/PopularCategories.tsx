import { Link } from '@inertiajs/react';
import { ArrowUpRight } from 'lucide-react';
import booksRoute from '@/routes/books';
import SectionHeader from './SectionHeader';
import type { WelcomeProps } from '@/features/welcome/types';

export default function PopularCategories({
    categories,
}: {
    categories: WelcomeProps['categories'];
}) {
    const popularCategories = [...categories]
        .filter((category) => category.booksCount > 0)
        .sort((first, second) => second.booksCount - first.booksCount)
        .slice(0, 6);

    if (popularCategories.length === 0) {
        return null;
    }

    return (
        <div className="flex flex-col gap-6">
            <SectionHeader
                title="Kategori Populer"
                subtitle="Kategori yang paling sering dilihat."
            />

            <div className="grid grid-cols-2 gap-3 lg:grid-cols-3">
                {popularCategories.map((category) => (
                    <Link
                        key={category.id}
                        href={booksRoute.index.url({
                            query: {
                                category: category.slug,
                            },
                        })}
                        className="group flex min-h-28 flex-col justify-between rounded-xl border bg-card p-4 transition-all duration-300 hover:border-primary/30 sm:min-h-32 sm:p-5"
                    >
                        <div className="flex items-start justify-between gap-4">
                            <div className="flex flex-col gap-2">
                                <h3 className="line-clamp-2 text-sm font-bold transition-colors group-hover:text-primary sm:text-base">
                                    {category.name}
                                </h3>
                                <p className="line-clamp-2 text-xs text-muted-foreground sm:text-sm">
                                    {category.description ||
                                        'Daftar buku pada kategori ini dapat dilihat di katalog.'}
                                </p>
                            </div>
                            <ArrowUpRight className="size-3.5 shrink-0 text-muted-foreground transition-colors group-hover:text-primary sm:size-4" />
                        </div>
                        <p className="mt-3 text-xs font-semibold text-primary sm:mt-4 sm:text-sm">
                            {category.booksCount.toLocaleString('id-ID')} judul
                        </p>
                    </Link>
                ))}
            </div>
        </div>
    );
}
