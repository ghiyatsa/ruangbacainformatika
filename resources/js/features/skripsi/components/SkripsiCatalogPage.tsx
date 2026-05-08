import { Deferred, Head } from '@inertiajs/react';
import { SkripsiFilters } from '@/features/skripsi/components/SkripsiFilters';
import { SkripsiGridSkeleton } from '@/features/skripsi/components/SkripsiGridSkeleton';
import { SkripsiHeader } from '@/features/skripsi/components/SkripsiHeader';
import { SkripsiResults } from '@/features/skripsi/components/SkripsiResults';
import type { SkripsiCatalogPageProps } from '@/features/skripsi/types';

export default function SkripsiCatalogPage({
    filters,
    years,
    total,
    skripsis,
}: SkripsiCatalogPageProps) {

    return (
        <>
            <Head title="Katalog Skripsi" />

            {/* Dot-grid background */}
            <div
                className="pointer-events-none fixed inset-0 z-0 opacity-[0.03] dark:opacity-[0.05]"
                style={{
                    backgroundImage:
                        'radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0)',
                    backgroundSize: '24px 24px',
                }}
            />

            <div className="relative z-10">
                <SkripsiHeader total={total} />

                {/* Main */}
                <section className="py-10">
                    <div className="mx-auto max-w-7xl px-6 lg:px-8">
                        <SkripsiFilters filters={filters} years={years} total={total} />

                        {/* Results */}
                        <Deferred
                            data="skripsis"
                            fallback={<SkripsiGridSkeleton />}
                        >
                            <SkripsiResults
                                skripsis={skripsis}
                            />
                        </Deferred>
                    </div>
                </section>
            </div>
        </>
    );
}
