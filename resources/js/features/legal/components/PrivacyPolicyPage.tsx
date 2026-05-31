import { LibraryPageHero } from '@/components/layouts/LibraryPageHero';
import { PageLayout } from '@/components/layouts/PageLayout';
import { StaticPageContent } from '@/components/layouts/StaticPageContent';
import type { StaticPageProps } from '@/features/static-pages/types';

export function PrivacyPolicyPage({ pageContent }: StaticPageProps) {
    return (
        <PageLayout
            title="Kebijakan Privasi"
            metaDescription={pageContent.summary}
            maxWidth="5xl"
            header={
                <LibraryPageHero
                    title="Kebijakan Privasi"
                    description={pageContent.summary}
                />
            }
        >
            <StaticPageContent html={pageContent.content} />
        </PageLayout>
    );
}
