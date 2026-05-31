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
import { CatalogReportCard } from '@/components/resource/CatalogReportCard';
import { ResourceDetailItem } from '@/components/resource/ResourceDetailItem';
import { ResourceDetailPage } from '@/components/resource/ResourceDetailPage';
import { ResourceDetailPageSkeleton } from '@/components/resource/ResourceDetailPageSkeleton';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCatalogBookmarks } from '@/hooks/use-catalog-bookmarks';
import { cn } from '@/lib/utils';
import skripsiRoute from '@/routes/skripsi';
import type { SkripsiShowProps } from '@/features/skripsi/types';
import type { CatalogBookmarkRecord } from '@/hooks/use-catalog-bookmarks';

export default function SkripsiDetailPage(
    props: SkripsiShowProps & { loading?: boolean },
) {
    const { isBookmarked, toggleBookmark } = useCatalogBookmarks();

    if (props.loading || !props.skripsi?.data) {
        return <ResourceDetailPageSkeleton contentTitle="Abstrak" />;
    }

    const {
        skripsi: { data: skripsi },
    } = props;
    const isBookmarkedByUser = isBookmarked({
        catalogType: 'skripsi',
        id: skripsi.id,
    });
    const bookmarkRecord: CatalogBookmarkRecord = {
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
    };

    return (
        <ResourceDetailPage
            title={skripsi.title}
            description={
                skripsi.abstract
                    ? skripsi.abstract.slice(0, 160)
                    : `${skripsi.title} tersedia di Ruang Baca Teknik Informatika Universitas Malikussaleh.`
            }
            hero={
                <div className="relative -mt-20 overflow-hidden border-b bg-linear-to-br from-primary/5 via-background to-muted/30 sm:-mt-28">
                    <div className="absolute inset-0 bg-linear-to-b from-background/0 via-background/40 to-background" />

                    <div className="relative mx-auto max-w-7xl px-6 pt-32 pb-12 sm:pt-40 lg:px-8">
                        <div className="mb-8">
                            <Breadcrumbs
                                breadcrumbs={[
                                    { title: 'Beranda', href: '/' },
                                    {
                                        title: 'Skripsi',
                                        href: skripsiRoute.index.url(),
                                    },
                                    {
                                        title: skripsi.studentId,
                                        href: skripsiRoute.show.url(
                                            skripsi.studentId,
                                        ),
                                    },
                                ]}
                            />
                        </div>

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
                                    <span className="text-border">&bull;</span>
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
                                    <span className="text-border">&bull;</span>
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            }
            sidebar={
                <div className="space-y-4">
                    <div className="rounded-2xl border bg-card/80 shadow-sm backdrop-blur-sm">
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
                        <div className="rounded-2xl border bg-card/80 shadow-sm backdrop-blur-sm">
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
            }
        >
            <section>
                <div className="mb-5 flex items-center gap-3">
                    <div className="flex size-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <BookMarked className="size-4" />
                    </div>
                    <h2 className="text-xl font-bold">Abstrak</h2>
                </div>

                {skripsi.abstract ? (
                    <div className="space-y-4 text-justify text-base leading-[1.85] text-muted-foreground">
                        {skripsi.abstract
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
                            Abstrak belum tersedia untuk skripsi ini.
                        </p>
                    </div>
                )}
            </section>
        </ResourceDetailPage>
    );
}
