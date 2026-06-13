import { Deferred } from '@inertiajs/react';
import {
    Bookmark,
    BookMarked,
    Calendar,
    Eye,
    Hash,
    Tag,
    User,
} from 'lucide-react';
import { Breadcrumbs } from '@/components/common/Breadcrumbs';
import { DeferredGlobalContentNotice } from '@/components/layouts/GlobalContentNotice';
import { CatalogReportCard } from '@/components/resource/CatalogReportCard';
import { CatalogResourceCardSkeleton } from '@/components/resource/CatalogResourceCardSkeleton';
import { CatalogShareButton } from '@/components/resource/CatalogShareButton';
import { RelatedCatalogSection } from '@/components/resource/RelatedCatalogSection';
import { ResourceDetailItem } from '@/components/resource/ResourceDetailItem';
import { ResourceDetailPage } from '@/components/resource/ResourceDetailPage';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import InternshipReportCard from '@/features/internship-report/components/InternshipReportCard';
import DeferredCatalogRescue from '@/features/welcome/components/catalog/DeferredCatalogRescue';
import { useCatalogBookmarks } from '@/hooks/use-catalog-bookmarks';
import { cn } from '@/lib/utils';
import internshipReportsRoute from '@/routes/internship-reports';
import type { InternshipReportShowProps } from '@/features/internship-report/types';
import type { CatalogBookmarkRecord } from '@/hooks/use-catalog-bookmarks';

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
        <ResourceDetailPage
            title={report?.title ?? 'Detail Laporan KP'}
            description={
                report?.abstract
                    ? report.abstract.slice(0, 160)
                    : report
                      ? `${report.title} tersedia di Ruang Baca Teknik Informatika Universitas Malikussaleh.`
                      : 'Memuat detail laporan KP dari katalog Ruang Baca Teknik Informatika Universitas Malikussaleh.'
            }
            keywords={seoKeywords}
            hero={
                <div className="relative -mt-20 overflow-hidden border-b bg-linear-to-br from-primary/5 via-background to-muted/30 sm:-mt-28">
                    <div className="absolute inset-0 bg-linear-to-b from-background/0 via-background/40 to-background" />

                    <div className="relative mx-auto max-w-7xl px-4 pt-24 pb-12 sm:px-6 sm:pt-30 lg:px-8">
                        <DeferredGlobalContentNotice className="hidden md:block" />
                        <div className="hidden sm:mb-6 sm:block">
                            <Breadcrumbs
                                breadcrumbs={[
                                    { title: 'Beranda', href: '/' },
                                    {
                                        title: 'Laporan KP',
                                        href: internshipReportsRoute.index.url(),
                                    },
                                    {
                                        title: report?.studentId ?? (
                                            <Skeleton className="h-4 w-24" />
                                        ),
                                        href: report
                                            ? internshipReportsRoute.show.url(
                                                  report.studentId,
                                              )
                                            : internshipReportsRoute.index.url(),
                                    },
                                ]}
                            />
                        </div>

                        {report ? (
                            <div className="flex flex-col gap-6 md:flex-row md:items-start md:gap-10">
                                <div className="flex flex-col justify-center">
                                    <h1 className="mb-3 text-2xl leading-tight font-bold tracking-tight sm:text-3xl lg:text-4xl">
                                        {report.title}
                                    </h1>

                                    <div className="flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                                        <span className="flex items-center gap-1.5">
                                            <User className="size-3.5" />
                                            {report.authorName}
                                        </span>
                                        <span className="text-border">
                                            &bull;
                                        </span>
                                        <span className="flex items-center gap-1.5">
                                            <Hash className="size-3.5" />
                                            NIM: {report.studentId}
                                        </span>
                                        {report.year ? (
                                            <>
                                                <span className="text-border">
                                                    &bull;
                                                </span>
                                                <span className="flex items-center gap-1.5">
                                                    <Calendar className="size-3.5" />
                                                    {report.year}
                                                </span>
                                            </>
                                        ) : null}
                                        <span className="text-border">
                                            &bull;
                                        </span>
                                        <span className="flex items-center gap-1.5">
                                            <Eye className="size-3.5" />
                                            {report.viewCount.toLocaleString(
                                                'id-ID',
                                            )}
                                        </span>
                                    </div>

                                    <div className="mt-5 flex flex-wrap items-center gap-3">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            className={cn(
                                                'h-auto gap-2 rounded-full px-4 py-2 text-sm font-medium',
                                                isBookmarkedByUser &&
                                                    'border-primary/40 bg-primary/10 text-primary hover:bg-primary/15',
                                            )}
                                            aria-label={
                                                isBookmarkedByUser
                                                    ? 'Hapus bookmark'
                                                    : 'Simpan bookmark'
                                            }
                                            aria-pressed={isBookmarkedByUser}
                                            onClick={() =>
                                                bookmarkRecord &&
                                                toggleBookmark(bookmarkRecord)
                                            }
                                        >
                                            <Bookmark
                                                className={
                                                    isBookmarkedByUser
                                                        ? 'fill-current'
                                                        : ''
                                                }
                                            />
                                            {isBookmarkedByUser
                                                ? 'Tersimpan'
                                                : 'Simpan'}
                                        </Button>

                                        <CatalogShareButton
                                            title={report.title}
                                            subtitle={report.authorName}
                                            kindLabel="Laporan KP"
                                        />
                                    </div>
                                </div>
                            </div>
                        ) : (
                            <div className="flex flex-col gap-6 md:flex-row md:items-start md:gap-10">
                                <div className="flex flex-1 flex-col justify-center">
                                    <h1 className="mb-3 text-2xl leading-tight font-bold tracking-tight sm:text-3xl lg:text-4xl">
                                        <Skeleton className="h-7 w-full max-w-3xl sm:h-8 lg:h-9 animate-pulse" />
                                        <Skeleton className="mt-2 h-7 w-4/5 max-w-2xl sm:h-8 lg:h-9 animate-pulse" />
                                    </h1>

                                    <div className="flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                                        <span className="flex items-center gap-1.5">
                                            <User className="size-3.5 text-muted-foreground/50" />
                                            <Skeleton className="h-4 w-32 animate-pulse" />
                                        </span>
                                        <span className="text-border">
                                            &bull;
                                        </span>
                                        <span className="flex items-center gap-1.5">
                                            <Hash className="size-3.5 text-muted-foreground/50" />
                                            <Skeleton className="h-4 w-24 animate-pulse" />
                                        </span>
                                        <span className="text-border">
                                            &bull;
                                        </span>
                                        <span className="flex items-center gap-1.5">
                                            <Calendar className="size-3.5 text-muted-foreground/50" />
                                            <Skeleton className="h-4 w-12 animate-pulse" />
                                        </span>
                                        <span className="text-border">
                                            &bull;
                                        </span>
                                        <span className="flex items-center gap-1.5">
                                            <Eye className="size-3.5 text-muted-foreground/50" />
                                            <Skeleton className="h-4 w-10 animate-pulse" />
                                        </span>
                                    </div>

                                    <div className="mt-5 flex flex-wrap items-center gap-3">
                                        <Skeleton className="h-10 w-28 rounded-full animate-pulse" />
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            }
            sidebar={
                <div className="space-y-4">
                    <div className="rounded-2xl border bg-card shadow-sm">
                        <div className="p-5">
                            <h2 className="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                                Informasi Laporan KP
                            </h2>
                        </div>
                        <Separator />
                        <div className="p-2">
                            {report ? (
                                <>
                                    <ResourceDetailItem
                                        icon={<User className="size-4" />}
                                        label="Penulis"
                                        value={report.authorName}
                                    />
                                    <ResourceDetailItem
                                        icon={<Hash className="size-4" />}
                                        label="NIM"
                                        value={report.studentId}
                                    />
                                    {report.year ? (
                                        <ResourceDetailItem
                                            icon={<Calendar className="size-4" />}
                                            label="Tahun"
                                            value={String(report.year)}
                                        />
                                    ) : null}
                                </>
                            ) : (
                                <>
                                    <ResourceDetailItem
                                        icon={<User className="size-4" />}
                                        label="Penulis"
                                        value={<Skeleton className="h-5 w-32 animate-pulse" />}
                                    />
                                    <ResourceDetailItem
                                        icon={<Hash className="size-4" />}
                                        label="NIM"
                                        value={<Skeleton className="h-5 w-24 animate-pulse" />}
                                    />
                                    <ResourceDetailItem
                                        icon={<Calendar className="size-4" />}
                                        label="Tahun"
                                        value={<Skeleton className="h-5 w-16 animate-pulse" />}
                                    />
                                </>
                            )}
                        </div>
                    </div>

                    {report ? (
                        report.keywords.length > 0 ? (
                            <div className="rounded-2xl border bg-card shadow-sm">
                                <div className="p-5">
                                    <h2 className="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                                        Kata Kunci
                                    </h2>
                                </div>
                                <Separator />
                                <div className="flex flex-wrap gap-2 p-4">
                                    {report.keywords.map((keyword) => (
                                        <Badge
                                            key={keyword}
                                            variant="secondary"
                                            className="gap-1 bg-muted/80"
                                        >
                                            <Tag className="size-2.5" />
                                            {keyword}
                                        </Badge>
                                    ))}
                                </div>
                            </div>
                        ) : null
                    ) : (
                        <div className="rounded-2xl border bg-card shadow-sm">
                            <div className="p-5">
                                <h2 className="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                                    Kata Kunci
                                </h2>
                            </div>
                            <Separator />
                            <div className="flex flex-wrap gap-2 p-4">
                                <Skeleton className="h-6 w-16 rounded-full animate-pulse" />
                                <Skeleton className="h-6 w-20 rounded-full animate-pulse" />
                                <Skeleton className="h-6 w-14 rounded-full animate-pulse" />
                                <Skeleton className="h-6 w-18 rounded-full animate-pulse" />
                            </div>
                        </div>
                    )}

                    {report && (
                        <CatalogReportCard
                            catalogType="internship_report"
                            catalogId={report.id}
                            catalogLabel="Laporan KP"
                            catalogTitle={report.title}
                        />
                    )}
                </div>
            }
            footer={
                (props.relatedReports === undefined || props.relatedReports.length > 0) && (
                    <RelatedCatalogSection
                        title="Laporan KP Terkait"
                        description="Daftar laporan kerja praktik lainnya dengan topik atau bidang pembahasan serupa."
                    >
                        <Deferred
                            data="relatedReports"
                            fallback={
                                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                    <CatalogResourceCardSkeleton />
                                    <CatalogResourceCardSkeleton />
                                    <CatalogResourceCardSkeleton />
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
                            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                {props.relatedReports?.map((relatedReport) => (
                                    <InternshipReportCard
                                        key={relatedReport.id}
                                        report={relatedReport}
                                    />
                                ))}
                            </div>
                        </Deferred>
                    </RelatedCatalogSection>
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
                    <div className="rounded-2xl border border-dashed bg-muted/30 p-10 text-center">
                        <BookMarked className="mx-auto mb-3 size-10 text-muted-foreground/40" />
                        <p className="text-sm text-muted-foreground">
                            Abstrak belum tersedia untuk laporan KP ini.
                        </p>
                    </div>
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
        </ResourceDetailPage>
    );
}

// test_compatibility: pt-24 pb-12 sm:pt-30
