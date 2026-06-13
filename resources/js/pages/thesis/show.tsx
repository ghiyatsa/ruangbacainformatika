import AcademicWorkDetailPage from '@/features/academic-works/components/AcademicWorkDetailPage';
import type { AcademicWorkData } from '@/features/academic-works/types';

interface ThesisShowProps {
    thesis: {
        data: AcademicWorkData;
    };
    relatedTheses?: AcademicWorkData[];
}

export default function ThesisShow({ thesis, relatedTheses, ...props }: ThesisShowProps) {
    return (
        <AcademicWorkDetailPage
            workType="thesis"
            academicWork={thesis}
            relatedWorks={relatedTheses}
            {...props}
        />
    );
}

