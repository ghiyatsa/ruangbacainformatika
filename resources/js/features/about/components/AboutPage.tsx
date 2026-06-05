import { LibraryPageHero } from '@/components/layouts/LibraryPageHero';
import { PageLayout } from '@/components/layouts/PageLayout';
import { StaticPageContent } from '@/components/layouts/StaticPageContent';
import type { StaticPageProps } from '@/features/static-pages/types';

export function AboutPage({ pageContent }: StaticPageProps) {
    return (
        <PageLayout
            title="Tentang Layanan"
            metaDescription={pageContent.summary}
            maxWidth="5xl"
            className="[&>div]:px-4 [&>div]:sm:px-6 [&>div]:lg:px-8"
            showDesktopNoticeInContent={false}
            header={
                <LibraryPageHero
                    eyebrow="Tentang Ruang Baca"
                    title={
                        <>
                            Tentang{' '}
                            <span className="bg-linear-to-r from-primary to-primary/75 bg-clip-text text-transparent">
                                Ruang Baca
                            </span>
                        </>
                    }
                    description={pageContent.summary}
                    contentClassName="max-w-5xl px-4 sm:px-6 lg:px-8"
                    align="center"
                />
            }
        >
            <StaticPageContent html={pageContent.content} />
        </PageLayout>
    );
}
