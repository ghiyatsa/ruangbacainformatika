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
                subtitle="Topik yang paling sering ditelusuri."
            />

            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                {popularCategories.map((category) => (
                    <Link
                        key={category.id}
                        href={booksRoute.index.url({
                            query: {
                                category: category.slug,
                            },
                        })}
                        className="group flex min-h-32 flex-col justify-between rounded-xl border bg-card p-5 transition-all duration-300 hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5"
                    >
                        <div className="flex items-start justify-between gap-4">
                            <div className="flex flex-col gap-2">
                                <h3 className="line-clamp-2 text-base font-bold transition-colors group-hover:text-primary">
                                    {category.name}
                                </h3>
                                <p className="line-clamp-2 text-sm text-muted-foreground">
                                    {category.description ||
                                        'Daftar referensi pada kategori ini.'}
                                </p>
                            </div>
                            <ArrowUpRight className="size-4 shrink-0 text-muted-foreground transition-colors group-hover:text-primary" />
                        </div>
                        <p className="mt-4 text-sm font-semibold text-primary">
                            {category.booksCount.toLocaleString('id-ID')}{' '}
                            judul
                        </p>
                    </Link>
                ))}
            </div>
        </div>
    );
}
