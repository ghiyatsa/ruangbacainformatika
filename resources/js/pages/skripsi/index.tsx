import AcademicWorkCatalogPage from '@/features/academic-works/components/AcademicWorkCatalogPage';
import type { PaginatedAcademicWorks, AcademicWorkFilters } from '@/features/academic-works/types';

interface SkripsiIndexProps {
    filters: AcademicWorkFilters;
    years: number[];
    total: number;
    skripsis: PaginatedAcademicWorks;
}

export default function SkripsiIndex({ skripsis, ...props }: SkripsiIndexProps) {
    return <AcademicWorkCatalogPage workType="skripsi" academicWorks={skripsis} {...props} />;
}

