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
import { useIsMobile } from '@/hooks/use-mobile';
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
    const isMobile = useIsMobile();

    if (categories.length === 0 || isMobile) {
        return null;
    }

    interface RowItem {
        category: MarqueeCategory;
        originalIndex: number;
    }

    const renderCategoryCard = (item: RowItem, isHiddenCopy = false) => {
        const Icon = icons[item.originalIndex % icons.length];

        return (
            <div className="py-2 sm:py-3">
                <Link
                    href={booksRoute.index.url({
                        query: {
                            category: item.category.slug,
                        },
                    })}
                    tabIndex={isHiddenCopy ? -1 : undefined}
                    className="relative flex w-56 flex-col rounded-2xl border bg-background p-4 text-left text-base whitespace-normal transition-all duration-300 group-hover/loop:opacity-40 hover:opacity-100! sm:w-72 sm:p-6"
                >
                    <div className="mb-3 flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary/5 text-primary transition-colors sm:mb-4 sm:size-12">
                        <Icon className="size-5 sm:size-6" />
                    </div>
                    <h3 className="mb-1.5 text-sm leading-tight font-bold text-foreground sm:mb-2 sm:text-base">
                        {item.category.name}
                    </h3>
                    <p className="line-clamp-2 text-xs leading-relaxed text-muted-foreground sm:text-sm">
                        {item.category.description ||
                            'Daftar buku dalam kategori ini.'}
                    </p>
                </Link>
            </div>
        );
    };

    const midPoint = Math.ceil(categories.length / 2);
    const row1: RowItem[] = categories
        .slice(0, midPoint)
        .map((cat, i) => ({ category: cat, originalIndex: i }));
    const row2: RowItem[] = categories
        .slice(midPoint)
        .map((cat, i) => ({ category: cat, originalIndex: i + midPoint }));

    const row2Items = row2.length > 0 ? row2 : row1;

    return (
        <section className="py-6 sm:py-8">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex flex-col overflow-hidden">
                    <h2 className="sr-only">Kategori buku unggulan</h2>
                    <VelocityScroll
                        items={row1}
                        speed={80}
                        direction="left"
                        pauseOnHover
                        fadeOut
                        gap={16}
                        className="group/loop"
                        renderItem={(item, key, isHiddenCopy) =>
                            renderCategoryCard(item as RowItem, isHiddenCopy)
                        }
                    />
                    <VelocityScroll
                        items={row2Items}
                        speed={80}
                        direction="right"
                        pauseOnHover
                        fadeOut
                        gap={16}
                        className="group/loop"
                        renderItem={(item, key, isHiddenCopy) =>
                            renderCategoryCard(item as RowItem, isHiddenCopy)
                        }
                    />
                </div>
            </div>
        </section>
    );
}
