import AcademicWorkCatalogPage from '@/features/academic-works/components/AcademicWorkCatalogPage';
import type { PaginatedAcademicWorks, AcademicWorkFilters } from '@/features/academic-works/types';

interface ThesisIndexProps {
    filters: AcademicWorkFilters;
    years: number[];
    total: number;
    theses: PaginatedAcademicWorks;
}

export default function ThesisIndex({ theses, ...props }: ThesisIndexProps) {
    return <AcademicWorkCatalogPage workType="thesis" academicWorks={theses} {...props} />;
}

