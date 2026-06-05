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
import { ResourceDetailPageSkeleton } from '@/components/resource/ResourceDetailPageSkeleton';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import ThesisCard from '@/features/thesis/components/ThesisCard';
import DeferredCatalogRescue from '@/features/welcome/components/catalog/DeferredCatalogRescue';
import { useCatalogBookmarks } from '@/hooks/use-catalog-bookmarks';
import { cn } from '@/lib/utils';
import thesisRoute from '@/routes/thesis';
import type { ThesisShowProps } from '@/features/thesis/types';
import type { CatalogBookmarkRecord } from '@/hooks/use-catalog-bookmarks';

export default function ThesisDetailPage(
    props: ThesisShowProps & { loading?: boolean },
) {
    const { isBookmarked, toggleBookmark } = useCatalogBookmarks();

    if (props.loading || !props.thesis?.data) {
        return <ResourceDetailPageSkeleton contentTitle="Abstrak" />;
    }

    const {
        thesis: { data: thesis },
    } = props;
    const isBookmarkedByUser = isBookmarked({
        catalogType: 'thesis',
        id: thesis.id,
    });
    const bookmarkRecord: CatalogBookmarkRecord = {
        catalogType: 'thesis',
        id: thesis.id,
        href: thesisRoute.show.url(thesis.studentId),
        title: thesis.title,
        subtitle: thesis.authorName,
        meta: `NIM: ${thesis.studentId}`,
        year: thesis.year,
        coverImageUrl: null,
        kindLabel: 'Tesis',
        statusLabel: null,
    };
    const seoKeywords = [
        thesis.title,
        thesis.authorName,
        thesis.studentId,
        ...(thesis.keywords ?? []),
        'tesis informatika',
        'ruang baca informatika',
    ].filter((value): value is string => Boolean(value));

    return (
        <ResourceDetailPage
            title={thesis.title}
            description={
                thesis.abstract
                    ? thesis.abstract.slice(0, 160)
                    : `${thesis.title} tersedia di Ruang Baca Teknik Informatika Universitas Malikussaleh.`
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
                                        title: 'Tesis',
                                        href: thesisRoute.index.url(),
                                    },
                                    {
                                        title: thesis.studentId,
                                        href: thesisRoute.show.url(
                                            thesis.studentId,
                                        ),
                                    },
                                ]}
                            />
                        </div>

                        <div className="flex flex-col gap-6 md:flex-row md:items-start md:gap-10">
                            <div className="flex flex-col justify-center">
                                <h1 className="mb-3 text-2xl leading-tight font-bold tracking-tight sm:text-3xl lg:text-4xl">
                                    {thesis.title}
                                </h1>

                                <div className="flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                                    <span className="flex items-center gap-1.5">
                                        <User className="size-3.5" />
                                        {thesis.authorName}
                                    </span>
                                    <span className="text-border">&bull;</span>
                                    <span className="flex items-center gap-1.5">
                                        <Hash className="size-3.5" />
                                        NIM: {thesis.studentId}
                                    </span>
                                    {thesis.year ? (
                                        <>
                                            <span className="text-border">
                                                &bull;
                                            </span>
                                            <span className="flex items-center gap-1.5">
                                                <Calendar className="size-3.5" />
                                                {thesis.year}
                                            </span>
                                        </>
                                    ) : null}
                                    <span className="text-border">&bull;</span>
                                    <span className="flex items-center gap-1.5">
                                        <Eye className="size-3.5" />
                                        {thesis.viewCount.toLocaleString(
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
                                        title={thesis.title}
                                        subtitle={thesis.authorName}
                                        kindLabel="Tesis"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            }
            sidebar={
                <div className="space-y-4">
                    <div className="rounded-2xl border bg-card shadow-sm">
                        <div className="p-5">
                            <h2 className="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                                Informasi Tesis
                            </h2>
                        </div>
                        <Separator />
                        <div className="p-2">
                            <ResourceDetailItem
                                icon={<User className="size-4" />}
                                label="Penulis"
                                value={thesis.authorName}
                            />
                            <ResourceDetailItem
                                icon={<Hash className="size-4" />}
                                label="NIM"
                                value={thesis.studentId}
                            />
                            {thesis.year ? (
                                <ResourceDetailItem
                                    icon={<Calendar className="size-4" />}
                                    label="Tahun"
                                    value={String(thesis.year)}
                                />
                            ) : null}
                        </div>
                    </div>

                    {thesis.keywords.length > 0 ? (
                        <div className="rounded-2xl border bg-card shadow-sm">
                            <div className="p-5">
                                <h2 className="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                                    Kata Kunci
                                </h2>
                            </div>
                            <Separator />
                            <div className="flex flex-wrap gap-2 p-4">
                                {thesis.keywords.map((keyword) => (
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
                        catalogType="thesis"
                        catalogId={thesis.id}
                        catalogLabel="Tesis"
                        catalogTitle={thesis.title}
                    />
                </div>
            }
            footer={
                <Deferred
                    data="relatedTheses"
                    fallback={<RelatedCatalogSectionSkeleton />}
                    rescue={({ reloading }) => (
                        <DeferredCatalogRescue
                            dataKey="relatedTheses"
                            title="Daftar tesis lain belum sempat dimuat"
                            description="Muat lagi sebentar kalau kamu ingin melihat tesis lain yang arahnya masih serupa."
                            reloading={reloading}
                        />
                    )}
                >
                    {props.relatedTheses && props.relatedTheses.length > 0 ? (
                        <RelatedCatalogSection
                            title="Tesis lain yang searah"
                            description="Kalau kamu sedang mendalami bahasan yang mirip, daftar ini bisa jadi titik lanjut yang pas."
                        >
                            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                {props.relatedTheses.map((relatedThesis) => (
                                    <ThesisCard
                                        key={relatedThesis.id}
                                        thesis={relatedThesis}
                                    />
                                ))}
                            </div>
                        </RelatedCatalogSection>
                    ) : null}
                </Deferred>
            }
        >
            <section>
                <div className="mb-5 flex items-center gap-3">
                    <div className="flex size-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <BookMarked className="size-4" />
                    </div>
                    <h2 className="text-xl font-bold">Abstrak</h2>
                </div>

                {thesis.abstract ? (
                    <div className="space-y-4 text-justify text-base leading-[1.85] text-muted-foreground">
                        {thesis.abstract
                            .split('\n')
                            .filter(Boolean)
                            .map((paragraph, index) => (
                                <p key={index}>{paragraph}</p>
                            ))}
                    </div>
                ) : (
                    <div className="rounded-2xl border border-dashed bg-muted/30 p-10 text-center">
                        <BookMarked className="mx-auto mb-3 size-10 text-muted-foreground/40" />
                        <p className="text-sm text-muted-foreground">
                            Abstrak belum tersedia untuk tesis ini.
                        </p>
                    </div>
                )}
            </section>
        </ResourceDetailPage>
    );
}

// test_compatibility: pt-24 pb-12 sm:pt-30
