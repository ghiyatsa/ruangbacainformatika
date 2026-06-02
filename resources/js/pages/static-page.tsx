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
            header={
                <LibraryPageHero
                    title={title}
                    description={pageContent.summary}
                />
            }
        >
            <StaticPageContent html={pageContent.content} />
        </PageLayout>
    );
}
