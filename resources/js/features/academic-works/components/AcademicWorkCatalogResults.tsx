import { BookMarked } from 'lucide-react';
import {
    Empty,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';
import AcademicWorkCard from '@/features/academic-works/components/AcademicWorkCard';
import type { PaginatedAcademicWorks } from '@/features/academic-works/types';

interface AcademicWorkCatalogResultsProps {
    workType: 'skripsi' | 'thesis';
    works: PaginatedAcademicWorks;
}

export function AcademicWorkCatalogResults({
    workType,
    works,
}: AcademicWorkCatalogResultsProps) {
    if (!works) {
        return null;
    }

    const label = workType === 'skripsi' ? 'Skripsi' : 'Tesis';

    return (
        <div className="flex flex-col gap-6">
            {works.data && works.data.length > 0 ? (
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-[repeat(auto-fill,minmax(250px,1fr))]">
                    {works.data.map((w) => (
                        <AcademicWorkCard key={w.id} work={w} workType={workType} />
                    ))}
                </div>
            ) : (
                <Empty className="border-2 py-20">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <BookMarked />
                        </EmptyMedia>
                        <EmptyTitle>{label} tidak ditemukan</EmptyTitle>
                        <EmptyDescription>
                            Coba kata kunci lain atau hapus filter yang aktif.
                        </EmptyDescription>
                    </EmptyHeader>
                </Empty>
            )}
        </div>
    );
}
