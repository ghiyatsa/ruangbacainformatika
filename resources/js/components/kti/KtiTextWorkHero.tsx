import { Bookmark, Calendar, Eye, Hash, User } from 'lucide-react';
import { Breadcrumbs } from '@/components/common/Breadcrumbs';
import { KtiShareButton } from '@/components/kti/KtiShareButton';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { cn, formatViewCount } from '@/lib/utils';
import type { CatalogBookmarkRecord } from '@/features/books/hooks/use-catalog-bookmarks';

export interface TextWorkRecord {
    id: number;
    title: string;
    authorName: string;
    studentId: string;
    year?: number | null;
    viewCount: number;
}

interface KtiTextWorkHeroProps {
    record: TextWorkRecord | null;
    label: string;
    kindLabel: string;
    indexUrl: string;
    detailUrl: string | null;
    isBookmarkedByUser: boolean;
    bookmarkRecord: CatalogBookmarkRecord | null;
    onToggleBookmark: (record: CatalogBookmarkRecord) => void;
}

export function KtiTextWorkHero({
    record,
    label,
    kindLabel,
    indexUrl,
    detailUrl,
    isBookmarkedByUser,
    bookmarkRecord,
    onToggleBookmark,
}: KtiTextWorkHeroProps) {
    return (
        <div className="relative -mt-20 overflow-hidden border-b bg-background sm:-mt-28 md:-mt-24">
            <div className="relative mx-auto max-w-7xl px-4 pt-24 pb-6 sm:px-6 sm:pt-30 sm:pb-8 lg:px-8">
                <div className="-mx-4 mb-6 hidden border-y border-border/60 bg-muted/5 px-4 py-3 sm:-mx-6 sm:flex sm:items-center sm:px-6 lg:-mx-8 lg:px-8">
                    <Breadcrumbs
                        breadcrumbs={[
                            { title: 'Beranda', href: '/' },
                            { title: label, href: indexUrl },
                            {
                                title: record?.studentId ?? (
                                    <Skeleton className="h-4 w-24" />
                                ),
                                href: detailUrl ?? indexUrl,
                            },
                        ]}
                    />
                </div>

                {record ? (
                    <div className="flex flex-col gap-6 md:flex-row md:items-start md:gap-10">
                        <div className="flex w-full flex-col justify-center">
                            <h1 className="mb-3 text-2xl leading-tight font-bold tracking-tight sm:text-3xl lg:text-4xl">
                                {record.title}
                            </h1>

                            <div className="flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                                <span className="flex items-center gap-1.5">
                                    <User className="size-3.5" />
                                    {record.authorName}
                                </span>
                                <span className="text-border">&bull;</span>
                                <span className="flex items-center gap-1.5">
                                    <Hash className="size-3.5" />
                                    NIM: {record.studentId}
                                </span>
                                {record.year ? (
                                    <>
                                        <span className="text-border">
                                            &bull;
                                        </span>
                                        <span className="flex items-center gap-1.5">
                                            <Calendar className="size-3.5" />
                                            {record.year}
                                        </span>
                                    </>
                                ) : null}
                                <span className="text-border">&bull;</span>
                                <span className="flex items-center gap-1.5">
                                    <Eye className="size-3.5" />
                                    {formatViewCount(record.viewCount)}
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
                                        onToggleBookmark(bookmarkRecord)
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
                                    title={record.title}
                                    subtitle={record.authorName}
                                    kindLabel={kindLabel}
                                />
                            </div>
                        </div>
                    </div>
                ) : (
                    <div className="flex flex-col gap-6 md:flex-row md:items-start md:gap-10">
                        <div className="flex flex-col justify-center">
                            <h1 className="mb-3 text-2xl leading-tight font-bold tracking-tight sm:text-3xl lg:text-4xl">
                                <Skeleton className="h-7 w-full max-w-3xl animate-pulse sm:h-8 lg:h-9" />
                                <Skeleton className="mt-2 h-7 w-4/5 max-w-2xl animate-pulse sm:h-8 lg:h-9" />
                            </h1>

                            <div className="flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                                <span className="flex items-center gap-1.5">
                                    <User className="size-3.5 text-muted-foreground/50" />
                                    <Skeleton className="h-4 w-32 animate-pulse" />
                                </span>
                                <span className="text-border">&bull;</span>
                                <span className="flex items-center gap-1.5">
                                    <Hash className="size-3.5 text-muted-foreground/50" />
                                    NIM:{' '}
                                    <Skeleton className="h-4 w-20 animate-pulse" />
                                </span>
                                <span className="text-border">&bull;</span>
                                <span className="flex items-center gap-1.5">
                                    <Calendar className="size-3.5 text-muted-foreground/50" />
                                    <Skeleton className="h-4 w-12 animate-pulse" />
                                </span>
                                <span className="text-border">&bull;</span>
                                <span className="flex items-center gap-1.5">
                                    <Eye className="size-3.5 text-muted-foreground/50" />
                                    <Skeleton className="h-4 w-10 animate-pulse" />
                                </span>
                            </div>

                            <div className="mt-5 flex flex-wrap items-center gap-3">
                                <Skeleton className="h-[38px] w-24 animate-pulse rounded-full" />
                                <Skeleton className="h-[38px] w-26 animate-pulse rounded-full" />
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
