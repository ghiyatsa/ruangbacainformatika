import { Deferred } from '@inertiajs/react';
import { BookMarked } from 'lucide-react';
import { KtiCardSkeleton } from '@/components/kti/KtiCardSkeleton';
import { KtiDetailPage } from '@/components/kti/KtiDetailPage';
import { KtiEmptyState } from '@/components/kti/KtiEmptyState';
import { KtiRelatedSection } from '@/components/kti/KtiRelatedSection';
import { KtiReportCard, KtiReportCardSkeleton } from '@/components/kti/KtiReportCard';
import { KtiTextWorkHero } from '@/components/kti/KtiTextWorkHero';
import { KtiTextWorkSidebar } from '@/components/kti/KtiTextWorkSidebar';
import { Skeleton } from '@/components/ui/skeleton';
import AcademicWorkCard from '@/features/academic-works/components/AcademicWorkCard';
import { useCatalogBookmarks } from '@/features/books/hooks/use-catalog-bookmarks';
import DeferredCatalogRescue from '@/features/welcome/components/DeferredCatalogRescue';
import skripsiRoute from '@/routes/skripsi';
import thesisRoute from '@/routes/thesis';
import type { AcademicWorkShowProps } from '@/features/academic-works/types';
import type { CatalogBookmarkRecord } from '@/features/books/hooks/use-catalog-bookmarks';

export default function AcademicWorkDetailPage(
    props: AcademicWorkShowProps & { workType: 'skripsi' | 'thesis'; loading?: boolean },
) {
    const { isBookmarked, toggleBookmark } = useCatalogBookmarks();
    const { workType } = props;

    const work = props.academicWork?.data ?? null;

    const isBookmarkedByUser = work
        ? isBookmarked({
              catalogType: workType,
              id: work.id,
          })
        : false;

    const route = workType === 'skripsi' ? skripsiRoute : thesisRoute;
    const label = workType === 'skripsi' ? 'Skripsi' : 'Tesis';
    const deferredDataKey = workType === 'skripsi' ? 'relatedSkripsis' : 'relatedTheses';

    const bookmarkRecord: CatalogBookmarkRecord | null = work
        ? {
              catalogType: workType,
              id: work.id,
              href: route.show.url(work.studentId),
              title: work.title,
              subtitle: work.authorName,
              meta: `NIM: ${work.studentId}`,
              year: work.year,
              coverImageUrl: null,
              kindLabel: label,
              statusLabel: null,
          }
        : null;

    const seoKeywords = work
        ? [
              work.title,
              work.authorName,
              work.studentId,
              ...(work.keywords ?? []),
              `${workType} informatika`,
              'ruang baca informatika',
          ].filter((value): value is string => Boolean(value))
        : [`${workType} informatika`, 'ruang baca informatika'];

    return (
        <KtiDetailPage
            title={work?.title ?? `Detail ${label}`}
            description={
                work?.abstract
                    ? work.abstract.slice(0, 160)
                    : work
                      ? `${work.title} tersedia di Ruang Baca Teknik Informatika Universitas Malikussaleh.`
                      : `Memuat detail ${workType === 'skripsi' ? 'skripsi' : 'tesis'} dari katalog Ruang Baca Teknik Informatika Universitas Malikussaleh.`
            }
            keywords={seoKeywords}
            showBackground={false}
            deferSecondaryContent
            contentClassName="pt-6 pb-10 sm:pt-8"
            hero={
                <KtiTextWorkHero
                    record={work}
                    label={label}
                    kindLabel={label}
                    indexUrl={route.index.url()}
                    detailUrl={
                        work
                            ? route.show.url(work.studentId)
                            : null
                    }
                    isBookmarkedByUser={isBookmarkedByUser}
                    bookmarkRecord={bookmarkRecord}
                    onToggleBookmark={toggleBookmark}
                />
            }
            sidebar={
                <KtiTextWorkSidebar
                    record={work}
                    label={label}
                />
            }
            secondarySidebar={
                work ? (
                    <KtiReportCard
                        catalogType={workType}
                        catalogId={work.id}
                        catalogLabel={label}
                        catalogTitle={work.title}
                    />
                ) : (
                    <KtiReportCardSkeleton />
                )
            }
            footer={
                (props.relatedWorks === undefined || props.relatedWorks.length > 0) && (
                    <KtiRelatedSection
                        title={`${label} Terkait`}
                    >
                        <Deferred
                            data={deferredDataKey}
                            fallback={
                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                    <KtiCardSkeleton />
                                    <KtiCardSkeleton />
                                    <KtiCardSkeleton />
                                </div>
                            }
                            rescue={({ reloading }) => (
                                <DeferredCatalogRescue
                                    dataKey={deferredDataKey}
                                    title={`Daftar ${workType === 'skripsi' ? 'skripsi' : 'tesis'} lain belum sempat dimuat`}
                                    description={`Muat lagi sebentar untuk melihat beberapa ${workType === 'skripsi' ? 'skripsi' : 'tesis'} yang bahasannya masih dekat dengan halaman ini.`}
                                    reloading={reloading}
                                />
                            )}
                        >
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                {props.relatedWorks?.map((relatedWork) => (
                                    <AcademicWorkCard
                                        key={relatedWork.id}
                                        work={relatedWork}
                                        workType={workType}
                                    />
                                ))}
                            </div>
                        </Deferred>
                    </KtiRelatedSection>
                )
            }
        >
                        {(() => {
                const jsonLd = work ? {
                    '@context': 'https://schema.org',
                    '@type': 'Thesis',
                    'name': work.title,
                    'headline': work.title,
                    'author': {
                        '@type': 'Person',
                        'name': work.authorName,
                        'identifier': work.studentId
                    },
                    'datePublished': work.year ? `${work.year}-01-01` : undefined,
                    'inLanguage': 'id',
                    'description': work.abstract || undefined,
                    'keywords': work.keywords ? work.keywords.join(', ') : undefined,
                    'learningResourceType': label,
                    'publisher': {
                        '@type': 'EducationalOrganization',
                        'name': 'Program Studi Teknik Informatika Universitas Malikussaleh',
                        'parentOrganization': {
                            '@type': 'EducationalOrganization',
                            'name': 'Universitas Malikussaleh'
                        }
                    }
                } : null;

                if (!jsonLd) {
return null;
}

                return (
                    <script type="application/ld+json">
                        {JSON.stringify(jsonLd)}
                    </script>
                );
            })()}

            <section>
                <div className="mb-5 flex items-center gap-3">
                    <h2 className="text-xl font-bold">Abstrak</h2>
                </div>

                {work?.abstract ? (
                    <div className="space-y-4 text-justify text-base leading-[1.85] text-muted-foreground">
                        {work.abstract
                            .split('\n')
                            .filter(Boolean)
                            .map((paragraph, index) => (
                                <p key={index}>{paragraph}</p>
                            ))}
                    </div>
                ) : work ? (
                    <KtiEmptyState
                        icon={BookMarked}
                        title={`Abstrak belum tersedia untuk ${workType === 'skripsi' ? 'skripsi' : 'tesis'} ini.`}
                    />
                ) : (
                    <div className="space-y-6">
                        <div className="space-y-3">
                            <Skeleton className="h-4 w-full" />
                            <Skeleton className="h-4 w-11/12" />
                            <Skeleton className="h-4 w-10/12" />
                            <Skeleton className="h-4 w-4/5" />
                        </div>
                        <div className="space-y-3">
                            <Skeleton className="h-4 w-full" />
                            <Skeleton className="h-4 w-full" />
                            <Skeleton className="h-4 w-5/6" />
                            <Skeleton className="h-4 w-2/3" />
                        </div>
                    </div>
                )}
            </section>
        </KtiDetailPage>
    );
}
