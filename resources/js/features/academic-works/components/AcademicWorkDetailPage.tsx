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
import { KtiCardSkeleton } from '@/components/kti/KtiCardSkeleton';
import { KtiDetailItem } from '@/components/kti/KtiDetailItem';
import { KtiDetailPage } from '@/components/kti/KtiDetailPage';
import { KtiRelatedSection } from '@/components/kti/KtiRelatedSection';
import { KtiReportCard } from '@/components/kti/KtiReportCard';
import { KtiShareButton } from '@/components/kti/KtiShareButton';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import AcademicWorkCard from '@/features/academic-works/components/AcademicWorkCard';
import { useCatalogBookmarks } from '@/features/books/hooks/use-catalog-bookmarks';
import DeferredCatalogRescue from '@/features/welcome/components/DeferredCatalogRescue';
import { cn } from '@/lib/utils';
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
            hero={
                <div className="relative -mt-20 overflow-hidden border-b bg-background sm:-mt-28 md:-mt-24">

                    <div className="relative mx-auto max-w-7xl px-4 pt-24 pb-12 sm:pt-30 sm:px-6 lg:px-8">
                        <div className="hidden sm:flex sm:items-center border-y border-border/60 py-3 mb-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 bg-muted/5">
                            <Breadcrumbs
                                breadcrumbs={[
                                    { title: 'Beranda', href: '/' },
                                    {
                                        title: label,
                                        href: route.index.url(),
                                    },
                                    {
                                        title: work?.studentId ?? (
                                            <Skeleton className="h-4 w-24" />
                                        ),
                                        href: work
                                            ? route.show.url(work.studentId)
                                            : route.index.url(),
                                    },
                                ]}
                            />
                        </div>

                        {work ? (
                            <div className="flex flex-col gap-6 md:flex-row md:items-start md:gap-10">
                                <div className="flex flex-1 flex-col justify-center">
                                    <h1 className="mb-3 text-2xl leading-tight font-bold tracking-tight sm:text-3xl lg:text-4xl">
                                        {work.title}
                                    </h1>

                                    <div className="flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                                        <span className="flex items-center gap-1.5">
                                            <User className="size-3.5" />
                                            {work.authorName}
                                        </span>
                                        <span className="text-border">
                                            &bull;
                                        </span>
                                        <span className="flex items-center gap-1.5">
                                            <Hash className="size-3.5" />
                                            NIM: {work.studentId}
                                        </span>
                                        {work.year ? (
                                            <>
                                                <span className="text-border">
                                                    &bull;
                                                </span>
                                                <span className="flex items-center gap-1.5">
                                                    <Calendar className="size-3.5" />
                                                    {work.year}
                                                </span>
                                            </>
                                        ) : null}
                                        <span className="text-border">
                                            &bull;
                                        </span>
                                        <span className="flex items-center gap-1.5">
                                            <Eye className="size-3.5" />
                                            {work.viewCount.toLocaleString('id-ID')}
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

                                        <KtiShareButton
                                            title={work.title}
                                            subtitle={work.authorName}
                                            kindLabel={label}
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
                    <div className="rounded-2xl border border-border/60 bg-transparent">
                        <div className="p-5">
                            <h2 className="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                                Informasi {label}
                            </h2>
                        </div>
                        <Separator />
                        <div className="p-2">
                            {work ? (
                                <>
                                    <KtiDetailItem
                                        icon={<User className="size-4" />}
                                        label="Penulis"
                                        value={work.authorName}
                                    />
                                    <KtiDetailItem
                                        icon={<Hash className="size-4" />}
                                        label="NIM"
                                        value={work.studentId}
                                    />
                                    {work.year ? (
                                        <KtiDetailItem
                                            icon={<Calendar className="size-4" />}
                                            label="Tahun"
                                            value={String(work.year)}
                                        />
                                    ) : null}
                                </>
                            ) : (
                                <>
                                    <KtiDetailItem
                                        icon={<User className="size-4" />}
                                        label="Penulis"
                                        value={<Skeleton className="h-5 w-32 animate-pulse" />}
                                    />
                                    <KtiDetailItem
                                        icon={<Hash className="size-4" />}
                                        label="NIM"
                                        value={<Skeleton className="h-5 w-24 animate-pulse" />}
                                    />
                                    <KtiDetailItem
                                        icon={<Calendar className="size-4" />}
                                        label="Tahun"
                                        value={<Skeleton className="h-5 w-16 animate-pulse" />}
                                    />
                                </>
                            )}
                        </div>
                    </div>

                    {work ? (
                        work.keywords.length > 0 ? (
                            <div className="rounded-2xl border border-border/60 bg-transparent">
                                <div className="p-5">
                                    <h2 className="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                                        Kata Kunci
                                    </h2>
                                </div>
                                <Separator />
                                <div className="flex flex-wrap gap-2 p-4">
                                    {work.keywords.map((keyword) => (
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
                        <div className="rounded-2xl border border-border/60 bg-transparent">
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

                    {work && (
                        <KtiReportCard
                            catalogType={workType}
                            catalogId={work.id}
                            catalogLabel={label}
                            catalogTitle={work.title}
                        />
                    )}
                </div>
            }
            footer={
                (props.relatedWorks === undefined || props.relatedWorks.length > 0) && (
                    <KtiRelatedSection
                        title={`${label} Terkait`}
                        description={`Daftar ${workType === 'skripsi' ? 'skripsi' : 'tesis'} lainnya dengan topik atau bidang penelitian serupa.`}
                    >
                        <Deferred
                            data={deferredDataKey}
                            fallback={
                                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
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
                            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
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
                    <div className="rounded-2xl border border-dashed bg-muted/30 p-10 text-center">
                        <BookMarked className="mx-auto mb-3 size-10 text-muted-foreground/40" />
                        <p className="text-sm text-muted-foreground">
                            Abstrak belum tersedia untuk {workType === 'skripsi' ? 'skripsi' : 'tesis'} ini.
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
        </KtiDetailPage>
    );
}
