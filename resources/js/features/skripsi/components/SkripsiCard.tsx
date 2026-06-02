import { Link } from '@inertiajs/react';
import {
    Bookmark,
    BookMarked,
    Calendar,
    ChevronRight,
    Hash,
    Tag,
    User,
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
} from '@/components/ui/card';
import { useCatalogBookmarks } from '@/hooks/use-catalog-bookmarks';
import { instantLoadingPageProps } from '@/lib/inertia-loading';
import { cn } from '@/lib/utils';
import skripsiRoute from '@/routes/skripsi';
import type { SkripsiData } from '@/features/skripsi/types';
import type { CatalogBookmarkRecord } from '@/hooks/use-catalog-bookmarks';

interface SkripsiCardProps {
    skripsi: SkripsiData;
}

export default function SkripsiCard({ skripsi }: SkripsiCardProps) {
    const { isBookmarked, toggleBookmark } = useCatalogBookmarks();
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
        <div className="group relative h-full">
            <Link
                href={skripsiRoute.show.url(skripsi.studentId)}
                instant
                component="skripsi/show"
                pageProps={instantLoadingPageProps()}
                className="absolute inset-0 z-10 rounded-xl focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:outline-none"
                aria-label={`Lihat detail skripsi ${skripsi.title}`}
            />
            <Card className="relative flex h-full flex-col overflow-hidden transition-all duration-300 hover:border-primary/30">
                <CardHeader className="gap-3">
                    <div className="flex items-start justify-between gap-3">
                        <Badge
                            variant="secondary"
                            className="gap-1 bg-primary/10 text-xs text-primary hover:bg-primary/15"
                        >
                            <BookMarked className="size-2.5" />
                            Skripsi
                        </Badge>

                        <div className="flex items-center gap-2">
                            {skripsi.year && (
                                <span className="flex items-center gap-1 text-[11px] text-muted-foreground">
                                    <Calendar className="size-3" />
                                    {skripsi.year}
                                </span>
                            )}
                            <Button
                                type="button"
                                size="icon-sm"
                                variant="outline"
                                className={cn(
                                    'relative z-20 shrink-0 rounded-full border-border/60 bg-background shadow-sm hover:border-primary/30 hover:bg-primary/5',
                                    isBookmarkedByUser &&
                                        'border-primary/40 bg-primary/10 text-primary hover:bg-primary/15',
                                )}
                                aria-label={
                                    isBookmarkedByUser
                                        ? 'Hapus bookmark'
                                        : 'Simpan bookmark'
                                }
                                title={
                                    isBookmarkedByUser
                                        ? 'Hapus bookmark'
                                        : 'Simpan bookmark'
                                }
                                aria-pressed={isBookmarkedByUser}
                                onClick={(event) => {
                                    event.preventDefault();
                                    event.stopPropagation();
                                    toggleBookmark(bookmarkRecord);
                                }}
                            >
                                <Bookmark
                                    className={
                                        isBookmarkedByUser ? 'fill-current' : ''
                                    }
                                />
                            </Button>
                        </div>
                    </div>

                    <div className="min-h-[3.75rem]">
                        <p className="line-clamp-3 text-sm leading-snug font-bold transition-colors group-hover:text-primary">
                            {skripsi.title}
                        </p>
                    </div>
                </CardHeader>

                <CardContent className="flex flex-1 flex-col gap-3">
                    <div className="min-h-[1.5rem]">
                        {skripsi.keywords.length > 0 && (
                            <div className="flex flex-wrap gap-1">
                                {skripsi.keywords.slice(0, 3).map((kw, i) => (
                                    <span
                                        key={i}
                                        className="inline-flex items-center gap-0.5 rounded-md bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground"
                                        data-skeleton-ignore
                                    >
                                        <Tag className="size-2.5" />
                                        {kw}
                                    </span>
                                ))}
                                {skripsi.keywords.length > 3 && (
                                    <span className="rounded-md bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground">
                                        +{skripsi.keywords.length - 3}
                                    </span>
                                )}
                            </div>
                        )}
                    </div>
                </CardContent>

                <CardFooter className="mt-auto flex min-h-[4.5rem] items-center justify-between border-t">
                    <div className="flex flex-col gap-0.5">
                        <span
                            className="flex items-center gap-1 text-xs font-semibold text-foreground"
                            title={skripsi.authorName}
                        >
                            <User className="size-3 text-muted-foreground" />
                            <span className="line-clamp-1">
                                {skripsi.authorName}
                            </span>
                        </span>
                        <span className="flex items-center gap-1 text-[10px] text-muted-foreground">
                            <Hash className="size-2.5" />
                            NIM: {skripsi.studentId}
                        </span>
                    </div>
                    <ChevronRight className="size-4 text-muted-foreground/50 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-primary" />
                </CardFooter>
            </Card>
        </div>
    );
}
