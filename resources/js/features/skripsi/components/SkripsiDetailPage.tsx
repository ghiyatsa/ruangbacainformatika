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
import { CatalogShareButton } from '@/components/resource/CatalogShareButton';
import { RelatedCatalogSection } from '@/components/resource/RelatedCatalogSection';
import { RelatedCatalogSectionSkeleton } from '@/components/resource/RelatedCatalogSectionSkeleton';
import { ResourceDetailItem } from '@/components/resource/ResourceDetailItem';
import { ResourceDetailPage } from '@/components/resource/ResourceDetailPage';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import SkripsiCard from '@/features/skripsi/components/SkripsiCard';
import DeferredCatalogRescue from '@/features/welcome/components/catalog/DeferredCatalogRescue';
import { useCatalogBookmarks } from '@/hooks/use-catalog-bookmarks';
import { cn } from '@/lib/utils';
import skripsiRoute from '@/routes/skripsi';
import type { SkripsiShowProps } from '@/features/skripsi/types';
import type { CatalogBookmarkRecord } from '@/hooks/use-catalog-bookmarks';

export default function SkripsiDetailPage(
    props: SkripsiShowProps & { loading?: boolean },
) {
    const { isBookmarked, toggleBookmark } = useCatalogBookmarks();

    const skripsi = props.skripsi?.data ?? null;

    const isBookmarkedByUser = skripsi
        ? isBookmarked({
              catalogType: 'skripsi',
              id: skripsi.id,
          })
        : false;

    const bookmarkRecord: CatalogBookmarkRecord | null = skripsi
        ? {
              catalogType: 'skripsi',
              id: skripsi.id,
              href: skripsiRoute.show.url(skripsi.studentId),
              title: skripsi.title,
              subtitle: skripsi.authorName,
              meta: `NIM: ${skripsi.studentId}`,
              year: skripsi.year,
              coverImageUrl: null,
              kindLabel: 'Skripsi',
              statusLabel: null,
          }
        : null;

    const seoKeywords = skripsi
        ? [
              skripsi.title,
              skripsi.authorName,
              skripsi.studentId,
              ...(skripsi.keywords ?? []),
              'skripsi informatika',
              'ruang baca informatika',
          ].filter((value): value is string => Boolean(value))
        : ['skripsi informatika', 'ruang baca informatika'];

    return (
        <ResourceDetailPage
            title={skripsi?.title ?? 'Detail Skripsi'}
            description={
                skripsi?.abstract
                    ? skripsi.abstract.slice(0, 160)
                    : skripsi
                      ? `${skripsi.title} tersedia di Ruang Baca Teknik Informatika Universitas Malikussaleh.`
                      : 'Memuat detail skripsi dari katalog Ruang Baca Teknik Informatika Universitas Malikussaleh.'
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
                                        title: 'Skripsi',
                                        href: skripsiRoute.index.url(),
                                    },
                                    {
                                        title: skripsi?.studentId ?? (
                                            <Skeleton className="h-4 w-24" />
                                        ),
                                        href: skripsi
                                            ? skripsiRoute.show.url(
                                                  skripsi.studentId,
                                              )
                                            : skripsiRoute.index.url(),
                                    },
                                ]}
                            />
                        </div>

                        {skripsi ? (
                            <div className="flex flex-col gap-6 md:flex-row md:items-start md:gap-10">
                                <div className="flex flex-col justify-center">
                                    <h1 className="mb-3 text-2xl leading-tight font-bold tracking-tight sm:text-3xl lg:text-4xl">
                                        {skripsi.title}
                                    </h1>

                                    <div className="flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                                        <span className="flex items-center gap-1.5">
                                            <User className="size-3.5" />
                                            {skripsi.authorName}
                                        </span>
                                        <span className="text-border">
                                            &bull;
                                        </span>
                                        <span className="flex items-center gap-1.5">
                                            <Hash className="size-3.5" />
                                            NIM: {skripsi.studentId}
                                        </span>
                                        {skripsi.year ? (
                                            <>
                                                <span className="text-border">
                                                    &bull;
                                                </span>
                                                <span className="flex items-center gap-1.5">
                                                    <Calendar className="size-3.5" />
                                                    {skripsi.year}
                                                </span>
                                            </>
                                        ) : null}
                                        <span className="text-border">
                                            &bull;
                                        </span>
                                        <span className="flex items-center gap-1.5">
                                            <Eye className="size-3.5" />
                                            {skripsi.viewCount.toLocaleString(
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
                                            title={skripsi.title}
                                            subtitle={skripsi.authorName}
                                            kindLabel="Skripsi"
                                        />
                                    </div>
                                </div>
                            </div>
                        ) : (
                            <div className="flex flex-col gap-6 md:flex-row md:items-start md:gap-10">
                                <div className="flex flex-1 flex-col justify-center">
                                    <div className="mb-3 space-y-3">
                                        <Skeleton className="h-8 w-full max-w-3xl" />
                                        <Skeleton className="h-8 w-4/5 max-w-2xl" />
                                    </div>

                                    <div className="flex flex-wrap items-center gap-3">
                                        <Skeleton className="h-4 w-34" />
                                        <Skeleton className="h-3 w-3 rounded-full" />
                                        <Skeleton className="h-4 w-28" />
                                        <Skeleton className="h-3 w-3 rounded-full" />
                                        <Skeleton className="h-4 w-14" />
                                        <Skeleton className="h-3 w-3 rounded-full" />
                                        <Skeleton className="h-4 w-12" />
                                    </div>

                                    <div className="mt-5 flex flex-wrap items-center gap-3">
                                        <Skeleton className="h-10 w-28 rounded-full" />
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            }
            sidebar={
                skripsi ? (
                    <div className="space-y-4">
                        <div className="rounded-2xl border bg-card shadow-sm">
                            <div className="p-5">
                                <h2 className="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                                    Informasi Skripsi
                                </h2>
                            </div>
                            <Separator />
                            <div className="p-2">
                                <ResourceDetailItem
                                    icon={<User className="size-4" />}
                                    label="Penulis"
                                    value={skripsi.authorName}
                                />
                                <ResourceDetailItem
                                    icon={<Hash className="size-4" />}
                                    label="NIM"
                                    value={skripsi.studentId}
                                />
                                {skripsi.year ? (
                                    <ResourceDetailItem
                                        icon={<Calendar className="size-4" />}
                                        label="Tahun"
                                        value={String(skripsi.year)}
                                    />
                                ) : null}
                            </div>
                        </div>

                        {skripsi.keywords.length > 0 ? (
                            <div className="rounded-2xl border bg-card shadow-sm">
                                <div className="p-5">
                                    <h2 className="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                                        Kata Kunci
                                    </h2>
                                </div>
                                <Separator />
                                <div className="flex flex-wrap gap-2 p-4">
                                    {skripsi.keywords.map((keyword) => (
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
                        ) : null}

                        <CatalogReportCard
                            catalogType="skripsi"
                            catalogId={skripsi.id}
                            catalogLabel="Skripsi"
                            catalogTitle={skripsi.title}
                        />
                    </div>
                ) : (
                    <div className="space-y-4">
                        <div className="rounded-2xl border bg-card shadow-sm">
                            <div className="p-5">
                                <Skeleton className="h-4 w-32" />
                            </div>
                            <Separator />
                            <div className="p-2">
                                {[0, 1, 2].map((item) => (
                                    <div
                                        key={item}
                                        className="flex items-start gap-3 rounded-xl p-3"
                                    >
                                        <Skeleton className="mt-0.5 size-8 rounded-lg" />
                                        <div className="min-w-0 flex-1 space-y-2">
                                            <Skeleton className="h-3 w-20" />
                                            <Skeleton className="h-4 w-full" />
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="rounded-2xl border bg-card shadow-sm">
                            <div className="p-5">
                                <Skeleton className="h-4 w-24" />
                            </div>
                            <Separator />
                            <div className="flex flex-wrap gap-2 p-4">
                                <Skeleton className="h-6 w-16 rounded-full" />
                                <Skeleton className="h-6 w-20 rounded-full" />
                                <Skeleton className="h-6 w-14 rounded-full" />
                                <Skeleton className="h-6 w-18 rounded-full" />
                            </div>
                        </div>
                    </div>
                )
            }
            footer={
                skripsi ? (
                    <Deferred
                        data="relatedSkripsis"
                        fallback={<RelatedCatalogSectionSkeleton />}
                        rescue={({ reloading }) => (
                            <DeferredCatalogRescue
                                dataKey="relatedSkripsis"
                                title="Daftar skripsi lain belum sempat dimuat"
                                description="Muat lagi sebentar untuk melihat beberapa skripsi yang bahasannya masih dekat dengan halaman ini."
                                reloading={reloading}
                            />
                        )}
                    >
                        {props.relatedSkripsis &&
                        props.relatedSkripsis.length > 0 ? (
                            <RelatedCatalogSection
                                title="Skripsi Terkait"
                                description="Daftar skripsi lainnya dengan topik atau bidang penelitian serupa."
                            >
                                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                    {props.relatedSkripsis.map(
                                        (relatedSkripsi) => (
                                            <SkripsiCard
                                                key={relatedSkripsi.id}
                                                skripsi={relatedSkripsi}
                                            />
                                        ),
                                    )}
                                </div>
                            </RelatedCatalogSection>
                        ) : null}
                    </Deferred>
                ) : null
            }
        >
            <section>
                <div className="mb-5 flex items-center gap-3">
                    <h2 className="text-xl font-bold">Abstrak</h2>
                </div>

                {skripsi?.abstract ? (
                    <div className="space-y-4 text-justify text-base leading-[1.85] text-muted-foreground">
                        {skripsi.abstract
                            .split('\n')
                            .filter(Boolean)
                            .map((paragraph, index) => (
                                <p key={index}>{paragraph}</p>
                            ))}
                    </div>
                ) : skripsi ? (
                    <div className="rounded-2xl border border-dashed bg-muted/30 p-10 text-center">
                        <BookMarked className="mx-auto mb-3 size-10 text-muted-foreground/40" />
                        <p className="text-sm text-muted-foreground">
                            Abstrak belum tersedia untuk skripsi ini.
                        </p>
                    </div>
                ) : (
                    <div className="space-y-3">
                        <Skeleton className="h-4 w-full" />
                        <Skeleton className="h-4 w-full" />
                        <Skeleton className="h-4 w-11/12" />
                        <Skeleton className="h-4 w-10/12" />
                        <Skeleton className="h-4 w-full" />
                        <Skeleton className="h-4 w-4/5" />
                        <Skeleton className="h-4 w-full" />
                        <Skeleton className="h-4 w-5/6" />
                    </div>
                )}
            </section>
        </ResourceDetailPage>
    );
}

// test_compatibility: pt-24 pb-12 sm:pt-30
