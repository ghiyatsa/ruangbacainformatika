import { BookMarked } from 'lucide-react';
import {
    Empty,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';
import { Separator } from '@/components/ui/separator';
import SkripsiCard from '@/features/skripsi/components/SkripsiCard';
import { SkripsiPagination } from '@/features/skripsi/components/SkripsiPagination';
import type { PaginatedSkripsis } from '@/features/skripsi/types';

interface SkripsiCatalogResultsProps {
    skripsis: PaginatedSkripsis;
}

export function SkripsiCatalogResults({ skripsis }: SkripsiCatalogResultsProps) {
    const total = skripsis.total ?? 0;

    return (
        <div className="flex flex-col gap-6">
            {skripsis.data && skripsis.data.length > 0 ? (
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {skripsis.data.map((s) => (
                        <SkripsiCard key={s.id} skripsi={s} />
                    ))}
                </div>
            ) : (
                <Empty className="border-2 py-20">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <BookMarked />
                        </EmptyMedia>
                        <EmptyTitle>Skripsi tidak ditemukan</EmptyTitle>
                        <EmptyDescription>
                            Coba kata kunci lain atau hapus filter yang aktif.
                        </EmptyDescription>
                    </EmptyHeader>
                </Empty>
            )}

            {total > 0 && (
                <>
                    <Separator />
                    <SkripsiPagination skripsis={skripsis} />
                </>
            )}
        </div>
    );
}
