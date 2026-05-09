import InternshipReportDetailPage from '@/features/internship-report/components/InternshipReportDetailPage';
import type { InternshipReportShowProps } from '@/features/internship-report/types';

export default function InternshipReportShow(props: InternshipReportShowProps) {
    return <InternshipReportDetailPage {...props} />;
}
