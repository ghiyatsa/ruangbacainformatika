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
                    contentClassName="max-w-4xl"
                    align="center"
                />
            }
        >
            <StaticPageContent html={pageContent.content} />
        </PageLayout>
    );
}
