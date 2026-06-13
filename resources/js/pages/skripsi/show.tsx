import AcademicWorkDetailPage from '@/features/academic-works/components/AcademicWorkDetailPage';
import type { AcademicWorkData } from '@/features/academic-works/types';

interface SkripsiShowProps {
    skripsi: {
        data: AcademicWorkData;
    };
    relatedSkripsis?: AcademicWorkData[];
}

export default function SkripsiShow({ skripsi, relatedSkripsis, ...props }: SkripsiShowProps) {
    return (
        <AcademicWorkDetailPage
            workType="skripsi"
            academicWork={skripsi}
            relatedWorks={relatedSkripsis}
            {...props}
        />
    );
}

