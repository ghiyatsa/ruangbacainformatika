import { LibraryPageHero } from '@/components/layouts/LibraryPageHero';
import { PageLayout } from '@/components/layouts/PageLayout';
import { StaticPageContent } from '@/components/layouts/StaticPageContent';
import type { StaticPageProps } from '@/features/static-pages/types';

interface StaticPageViewProps extends StaticPageProps {
    title: string;
}

export default function StaticPage({
    title,
    pageContent,
}: StaticPageViewProps) {
    return (
        <PageLayout
            title={title}
            metaDescription={pageContent.summary}
            maxWidth="5xl"
            className="[&>div]:px-4 [&>div]:sm:px-6 [&>div]:lg:px-8"
            showDesktopNoticeInContent={false}
            header={
                <LibraryPageHero
                    title={title}
                    description={pageContent.summary}
                    contentClassName="max-w-5xl px-4 sm:px-6 lg:px-8"
                />
            }
        >
            <StaticPageContent html={pageContent.content} />
        </PageLayout>
    );
}
