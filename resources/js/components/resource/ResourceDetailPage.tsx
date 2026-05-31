import { SeoHead } from '@/components/common/SeoHead';
import { BackgroundPattern } from '@/components/layouts/BackgroundPattern';
import type { ReactNode } from 'react';

interface ResourceDetailPageProps {
    title: string;
    description?: string;
    image?: string;
    keywords?: string | string[];
    hero: ReactNode;
    sidebar: ReactNode;
    children: ReactNode;
}

export function ResourceDetailPage({
    title,
    description,
    image,
    keywords,
    hero,
    sidebar,
    children,
}: ResourceDetailPageProps) {
    return (
        <>
            <SeoHead
                title={title}
                description={description}
                image={image}
                keywords={keywords}
                type="article"
            />
            <BackgroundPattern />

            <div className="relative z-10 flex flex-col">
                {hero}

                <div className="py-10">
                    <div className="mx-auto max-w-7xl px-6 lg:px-8">
                        <div className="grid gap-8 md:grid-cols-12 md:gap-10">
                            <aside className="order-2 md:order-1 md:col-span-4 lg:col-span-3">
                                {sidebar}
                            </aside>

                            <div className="order-1 md:order-2 md:col-span-8 lg:col-span-9">
                                {children}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
