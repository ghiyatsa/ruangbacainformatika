import { BrowserSessionsSection } from '@/features/settings/components/security/BrowserSessionsSection';
import type { SecurityPageProps } from '@/features/settings/types';

export default function SecurityPage({ sessions = [] }: SecurityPageProps) {
    return <BrowserSessionsSection sessions={sessions} />;
}
