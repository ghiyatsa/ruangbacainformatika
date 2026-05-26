import { Link } from '@inertiajs/react';
import {
    BookMarked,
    Code,
    Cpu,
    Globe,
    Layers,
    Library,
    Network,
    Terminal,
} from 'lucide-react';

import VelocityScroll from '@/components/common/VelocityScroll';
import booksRoute from '@/routes/books';

interface CategoryMarqueeProps {
    categories?: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        booksCount: number;
    }[];
}

const icons = [
    Cpu,
    Code,
    Globe,
    Layers,
    BookMarked,
    Library,
    Network,
    Terminal,
];

type MarqueeCategory = NonNullable<CategoryMarqueeProps['categories']>[number];

export default function CategoryMarquee({
    categories = [],
}: CategoryMarqueeProps) {
    if (categories.length === 0) {
        return null;
    }

    const renderCategoryCard = (category: MarqueeCategory, index: number) => {
        const Icon = icons[index % icons.length];

        return {
            node: (
                <div className="py-2 sm:py-3">
                    <Link
                        href={booksRoute.index.url({
                            query: {
                                category: category.slug,
                            },
                        })}
                        className="relative flex w-56 flex-col rounded-2xl border bg-background p-4 text-left text-base whitespace-normal transition-all duration-300 group-hover/loop:opacity-40 hover:opacity-100! sm:w-72 sm:p-6"
                    >
                        <div className="mb-3 flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary/5 text-primary transition-colors sm:mb-4 sm:size-12">
                            <Icon className="size-5 sm:size-6" />
                        </div>
                        <h3 className="mb-1.5 text-sm leading-tight font-bold text-foreground sm:mb-2 sm:text-base">
                            {category.name}
                        </h3>
                        <p className="line-clamp-2 text-xs leading-relaxed text-muted-foreground sm:text-sm">
                            {category.description ||
                                'Buku pilihan dalam kategori ini.'}
                        </p>
                    </Link>
                </div>
            ),
        };
    };

    const midPoint = Math.ceil(categories.length / 2);
    const row1 = categories
        .slice(0, midPoint)
        .map((cat, i) => renderCategoryCard(cat, i));
    const row2 = categories
        .slice(midPoint)
        .map((cat, i) => renderCategoryCard(cat, i + midPoint));

    const row2Items = row2.length > 0 ? row2 : row1;

    return (
        <section className="py-6 sm:py-8">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex flex-col overflow-hidden">
                    <VelocityScroll
                        items={row1}
                        speed={30}
                        direction="left"
                        pauseOnHover
                        fadeOut
                        gap={16}
                        className="group/loop"
                    />
                    <VelocityScroll
                        items={row2Items}
                        speed={30}
                        direction="right"
                        pauseOnHover
                        fadeOut
                        gap={16}
                        className="group/loop"
                    />
                </div>
            </div>
        </section>
    );
}
