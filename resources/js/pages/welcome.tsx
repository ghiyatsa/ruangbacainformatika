import { Head } from '@inertiajs/react';

import { AppHeader } from '@/components/app-header';
import CatalogSection from '@/components/welcome/CatalogSection';
import DomainHighlights from '@/components/welcome/DomainHighlights';
import Footer from '@/components/welcome/Footer';
import Hero from '@/components/welcome/Hero';
import type { WelcomeProps } from '@/components/welcome/types';

export default function Welcome({
    stats,
    featuredBooks,
    books,
    categories,
}: WelcomeProps) {
    return (
        <div className="min-h-screen bg-background font-sans text-foreground selection:bg-primary/10 selection:text-primary">
            <Head title="Ruang Baca — Digital Library of Informatics Engineering UNIMAL" />

            {/* Pattern Overlay */}
            <div
                className="pointer-events-none fixed inset-0 z-0 opacity-[0.03] dark:opacity-[0.05]"
                style={{
                    backgroundImage:
                        'radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0)',
                    backgroundSize: '24px 24px',
                }}
            />

            <div className="relative z-10 flex min-h-screen flex-col">
                <AppHeader />

                <main className="flex-1">
                    <Hero stats={stats} />

                    <DomainHighlights categories={categories} />

                    <CatalogSection
                        stats={stats}
                        featuredBooks={featuredBooks}
                        books={books}
                    />
                </main>

                <Footer />
            </div>
        </div>
    );
}
