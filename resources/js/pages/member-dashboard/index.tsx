import { MemberDashboard } from '@/features/dashboard/components/MemberDashboard';
import type {
    MemberDashboardCounts,
    MemberSubmissionItem,
} from '@/features/dashboard/components/MemberDashboard';

interface DashboardIndexProps {
    counts: MemberDashboardCounts;
    submissions: MemberSubmissionItem[];
}

export default function DashboardIndex({
    counts,
    submissions,
}: DashboardIndexProps) {
    return <MemberDashboard counts={counts} submissions={submissions} />;
}
