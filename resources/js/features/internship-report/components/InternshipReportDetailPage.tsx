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
import { useCatalogBookmarks } from '@/features/books/hooks/use-catalog-bookmarks';
import InternshipReportCard from '@/features/internship-report/components/InternshipReportCard';
import DeferredCatalogRescue from '@/features/welcome/components/DeferredCatalogRescue';
import internshipReportsRoute from '@/routes/internship-reports';
import type { CatalogBookmarkRecord } from '@/features/books/hooks/use-catalog-bookmarks';
import type { InternshipReportShowProps } from '@/features/internship-report/types';

export default function InternshipReportDetailPage(
    props: InternshipReportShowProps & { loading?: boolean },
) {
    const { isBookmarked, toggleBookmark } = useCatalogBookmarks();

    const report = props.report?.data ?? null;
    const isBookmarkedByUser = report
        ? isBookmarked({
              catalogType: 'internship_report',
              id: report.id,
          })
        : false;
    const bookmarkRecord: CatalogBookmarkRecord | null = report
        ? {
              catalogType: 'internship_report',
              id: report.id,
              href: internshipReportsRoute.show.url(report.studentId),
              title: report.title,
              subtitle: report.authorName,
              meta: `NIM: ${report.studentId}`,
              year: report.year,
              coverImageUrl: null,
              kindLabel: 'Laporan KP',
              statusLabel: null,
          }
        : null;
    const seoKeywords = report
        ? [
              report.title,
              report.authorName,
              report.studentId,
              ...(report.keywords ?? []),
              'laporan kerja praktik informatika',
              'ruang baca informatika',
          ].filter((value): value is string => Boolean(value))
        : ['laporan kerja praktik informatika', 'ruang baca informatika'];

    return (
        <KtiDetailPage
            title={report?.title ?? 'Detail Laporan KP'}
            description={
                report?.abstract
                    ? report.abstract.slice(0, 160)
                    : report
                      ? `${report.title} tersedia di Ruang Baca Teknik Informatika Universitas Malikussaleh.`
                      : 'Memuat detail laporan KP dari katalog Ruang Baca Teknik Informatika Universitas Malikussaleh.'
            }
            keywords={seoKeywords}
            showBackground={false}
            deferSecondaryContent
            contentClassName="pt-6 pb-10 sm:pt-8"
            hero={
                <KtiTextWorkHero
                    record={report}
                    label="Laporan KP"
                    kindLabel="Laporan KP"
                    indexUrl={internshipReportsRoute.index.url()}
                    detailUrl={
                        report
                            ? internshipReportsRoute.show.url(report.studentId)
                            : null
                    }
                    isBookmarkedByUser={isBookmarkedByUser}
                    bookmarkRecord={bookmarkRecord}
                    onToggleBookmark={toggleBookmark}
                />
            }
            sidebar={
                <KtiTextWorkSidebar
                    record={report}
                    label="Laporan KP"
                />
            }
            secondarySidebar={
                report ? (
                    <KtiReportCard
                        catalogType="internship_report"
                        catalogId={report.id}
                        catalogLabel="Laporan KP"
                        catalogTitle={report.title}
                    />
                ) : (
                    <KtiReportCardSkeleton />
                )
            }
            footer={
                (props.relatedReports === undefined ||
                    props.relatedReports.length > 0) && (
                    <KtiRelatedSection
                        title="Laporan KP Terkait"
                    >
                        <Deferred
                            data="relatedReports"
                            fallback={
                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
                                    <KtiCardSkeleton />
                                    <KtiCardSkeleton />
                                    <KtiCardSkeleton />
                                </div>
                            }
                            rescue={({ reloading }) => (
                                <DeferredCatalogRescue
                                    dataKey="relatedReports"
                                    title="Daftar laporan lain belum sempat dimuat"
                                    description="Muat lagi sebentar untuk melihat beberapa laporan KP yang masih dekat dengan topik ini."
                                    reloading={reloading}
                                />
                            )}
                        >
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
                                {props.relatedReports?.map((relatedReport) => (
                                    <InternshipReportCard
                                        key={relatedReport.id}
                                        report={relatedReport}
                                    />
                                ))}
                            </div>
                        </Deferred>
                    </KtiRelatedSection>
                )
            }
        >
            <section>
                <div className="mb-5 flex items-center gap-3">
                    <h2 className="text-xl font-bold">Abstrak</h2>
                </div>

                {report?.abstract ? (
                    <div className="space-y-4 text-justify text-base leading-[1.85] text-muted-foreground">
                        {report.abstract
                            .split('\n')
                            .filter(Boolean)
                            .map((paragraph, index) => (
                                <p key={index}>{paragraph}</p>
                            ))}
                    </div>
                ) : report ? (
                    <KtiEmptyState
                        icon={BookMarked}
                        title="Abstrak belum tersedia untuk laporan KP ini."
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
