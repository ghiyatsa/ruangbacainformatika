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

import LogoLoop from '@/components/common/LogoLoop';

interface DomainHighlightsProps {
    categories?: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
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

export default function DomainHighlights({
    categories = [],
}: DomainHighlightsProps) {
    const items = categories.map((category, index) => {
        const Icon = icons[index % icons.length];

        return {
            node: (
                <div className="py-3 sm:py-4">
                    <Link
                        href={`/katalog?category=${category.slug}`}
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
                                'Jelajahi koleksi literatur dan referensi pada kategori ini.'}
                        </p>
                    </Link>
                </div>
            ),
        };
    });

    if (items.length === 0) {
        return null;
    }

    return (
        <section className="py-6 sm:py-8">
            <div className="container mx-auto">
                <LogoLoop
                    logos={items}
                    speed={30}
                    pauseOnHover
                    fadeOut
                    logoHeight={160}
                    gap={16}
                    className="group/loop"
                />
            </div>
        </section>
    );
}
