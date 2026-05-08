import { Deferred, router } from '@inertiajs/react';
import { Search, X } from 'lucide-react';
import { CatalogPageLayout } from '@/components/catalog/CatalogPageLayout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { SkripsiCatalogFilters } from '@/features/skripsi/components/SkripsiCatalogFilters';
import { SkripsiCatalogHeader } from '@/features/skripsi/components/SkripsiCatalogHeader';
import { SkripsiCatalogResults } from '@/features/skripsi/components/SkripsiCatalogResults';
import { SkripsiGridSkeleton } from '@/features/skripsi/components/SkripsiGridSkeleton';
import type { SkripsiCatalogPageProps } from '@/features/skripsi/types';
import skripsiRoute from '@/routes/skripsi';

export default function SkripsiCatalogPage({
    filters,
    years,
    total,
    skripsis,
}: SkripsiCatalogPageProps) {
    function clearAllFilters(): void {
        router.get(
            skripsiRoute.index.url(),
            {},
            { preserveScroll: true, replace: true },
        );
    }

    return (
        <CatalogPageLayout
            title="Katalog Skripsi"
            header={<SkripsiCatalogHeader total={total} />}
        >
            <div className="flex flex-col gap-6">
                <SkripsiCatalogFilters
                    filters={filters}
                    years={years}
                    total={total}
                />

                {/* Active Search Badge */}
                {filters.search && (
                    <div className="flex items-center gap-2">
                        <Badge
                            variant="secondary"
                            className="gap-1.5 py-1.5 pr-2 pl-2.5"
                        >
                            <Search className="size-3 text-muted-foreground" />
                            <span className="text-muted-foreground">
                                Hasil pencarian:
                            </span>
                            &ldquo;{filters.search}&rdquo;
                            <button
                                onClick={clearAllFilters}
                                className="ml-1 rounded-full p-0.5 transition-colors hover:bg-muted"
                            >
                                <X className="size-3" />
                            </button>
                        </Badge>
                        <Button
                            variant="ghost"
                            size="sm"
                            className="h-8 text-xs text-muted-foreground"
                            onClick={clearAllFilters}
                        >
                            Hapus semua filter
                        </Button>
                    </div>
                )}
            </div>

            {/* Results */}
            <Deferred data="skripsis" fallback={<SkripsiGridSkeleton />}>
                <SkripsiCatalogResults skripsis={skripsis} />
            </Deferred>
        </CatalogPageLayout>
    );
}
