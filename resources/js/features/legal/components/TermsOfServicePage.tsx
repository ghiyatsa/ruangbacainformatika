import { PageLayout } from '@/components/layout/PageLayout';
import { PublicPageHero } from '@/components/layout/PublicPageHero';
import { StaticPageContent } from '@/components/layout/StaticPageContent';
import type { StaticPageProps } from '@/features/static-pages/types';

export function TermsOfServicePage({ pageContent }: StaticPageProps) {
    return (
        <PageLayout
            title="Syarat Layanan"
            metaDescription={pageContent.summary}
            maxWidth="5xl"
            className="[&>div]:px-4 [&>div]:sm:px-6 [&>div]:lg:px-8"
            showDesktopNoticeInContent={false}
            header={
                <PublicPageHero
                    title="Syarat Layanan"
                    description={pageContent.summary}
                    contentClassName="max-w-5xl px-4 sm:px-6 lg:px-8"
                />
            }
        >
            <StaticPageContent html={pageContent.content} />
        </PageLayout>
    );
}
