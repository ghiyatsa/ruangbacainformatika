import { BookMarked } from 'lucide-react';
import {
    Empty,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';
import ThesisCard from '@/features/thesis/components/ThesisCard';
import type { PaginatedTheses } from '@/features/thesis/types';

interface ThesisCatalogResultsProps {
    theses: PaginatedTheses;
}

export function ThesisCatalogResults({ theses }: ThesisCatalogResultsProps) {
    if (!theses) {
        return null;
    }

    return (
        <div className="flex flex-col gap-6">
            {theses.data && theses.data.length > 0 ? (
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
                    {theses.data.map((t) => (
                        <ThesisCard key={t.id} thesis={t} />
                    ))}
                </div>
            ) : (
                <Empty className="border-2 py-20">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <BookMarked />
                        </EmptyMedia>
                        <EmptyTitle>Tesis tidak ditemukan</EmptyTitle>
                        <EmptyDescription>
                            Coba kata kunci lain atau hapus filter yang aktif.
                        </EmptyDescription>
                    </EmptyHeader>
                </Empty>
            )}
        </div>
    );
}
