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
                <div className="py-4">
                    <Link
                        href={`/katalog?category=${category.slug}`}
                        className="relative flex w-72 flex-col rounded-2xl border bg-background p-6 text-left text-base whitespace-normal transition-all duration-300 group-hover/loop:opacity-40 hover:opacity-100!"
                    >
                        <div className="mb-4 flex size-12 shrink-0 items-center justify-center rounded-xl bg-primary/5 text-primary transition-colors">
                            <Icon className="size-6" />
                        </div>
                        <h3 className="mb-2 leading-tight font-bold text-foreground">
                            {category.name}
                        </h3>
                        <p className="line-clamp-2 text-sm leading-relaxed text-muted-foreground">
                            {category.description ||
                                'Jelajahi koleksi literatur dan referensi pada kategori ini.'}
                        </p>
                    </Link>
                </div>
            ),
        };
    });

    return (
        <section className="py-8">
            <div className="container mx-auto px-6 lg:px-8">
                {items.length > 0 ? (
                    <LogoLoop
                        logos={items}
                        speed={30}
                        pauseOnHover
                        fadeOut
                        logoHeight={180}
                        gap={24}
                        className="group/loop"
                    />
                ) : (
                    <div className="text-center text-sm text-muted-foreground">
                        Belum ada kategori tersedia.
                    </div>
                )}
            </div>
        </section>
    );
}
