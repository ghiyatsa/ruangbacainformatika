import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { BackgroundPattern } from '@/components/layouts/BackgroundPattern';

interface ResourceDetailPageProps {
    title: string;
    hero: ReactNode;
    sidebar: ReactNode;
    children: ReactNode;
}

export function ResourceDetailPage({
    title,
    hero,
    sidebar,
    children,
}: ResourceDetailPageProps) {
    return (
        <>
            <Head title={title} />
            <BackgroundPattern />

            <div className="relative z-10 flex flex-col">
                {hero}

                <div className="py-10">
                    <div className="mx-auto max-w-7xl px-6 lg:px-8">
                        <div className="grid gap-8 md:grid-cols-12 md:gap-10">
                            <aside className="md:col-span-4 lg:col-span-3">
                                {sidebar}
                            </aside>

                            <div className="md:col-span-8 lg:col-span-9">
                                {children}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
