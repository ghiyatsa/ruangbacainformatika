import { Link } from '@inertiajs/react';
import {
    Bookmark,
    Calendar,
    ChevronRight,
    GraduationCap,
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
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import type { ThesisData } from '@/features/thesis/types';
import type { CatalogBookmarkRecord } from '@/hooks/use-catalog-bookmarks';
import { useCatalogBookmarks } from '@/hooks/use-catalog-bookmarks';
import { cn } from '@/lib/utils';
import thesisRoute from '@/routes/thesis';

interface ThesisCardProps {
    thesis: ThesisData;
}

export default function ThesisCard({ thesis }: ThesisCardProps) {
    const { isBookmarked, toggleBookmark } = useCatalogBookmarks();
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

    return (
        <div className="group relative h-full">
            <Link
                href={thesisRoute.show.url(thesis.studentId)}
                className="absolute inset-0 z-10 rounded-xl focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:outline-none"
                aria-label={`Lihat detail tesis ${thesis.title}`}
            />
            <Card className="relative flex h-full flex-col overflow-hidden transition-all duration-300 hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5 dark:hover:shadow-primary/10">
                <CardHeader className="gap-3">
                    <div className="flex items-start justify-between gap-3">
                        <Badge
                            variant="secondary"
                            className="gap-1 bg-primary/10 text-xs text-primary hover:bg-primary/15"
                        >
                            <GraduationCap className="size-2.5" />
                            Tesis
                        </Badge>

                        <div className="flex items-center gap-2">
                            {thesis.year && (
                                <span className="flex items-center gap-1 text-[11px] text-muted-foreground">
                                    <Calendar className="size-3" />
                                    {thesis.year}
                                </span>
                            )}
                            <Tooltip>
                                <TooltipTrigger asChild>
                                    <Button
                                        type="button"
                                        size="icon-sm"
                                        variant="outline"
                                        className={cn(
                                            'relative z-20 shrink-0 rounded-full border-border/60 bg-background/90 shadow-sm backdrop-blur-sm hover:border-primary/30 hover:bg-primary/5',
                                            isBookmarkedByUser &&
                                                'border-primary/40 bg-primary/10 text-primary hover:bg-primary/15',
                                        )}
                                        aria-label={
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
                                                isBookmarkedByUser
                                                    ? 'fill-current'
                                                    : ''
                                            }
                                        />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent side="bottom" sideOffset={8}>
                                    {isBookmarkedByUser
                                        ? 'Hapus bookmark'
                                        : 'Simpan bookmark'}
                                </TooltipContent>
                            </Tooltip>
                        </div>
                    </div>

                    <div className="min-h-[3.75rem]">
                        <h3 className="line-clamp-3 text-sm leading-snug font-bold transition-colors group-hover:text-primary">
                            {thesis.title}
                        </h3>
                    </div>
                </CardHeader>

                <CardContent className="flex flex-1 flex-col gap-3">
                    <div className="min-h-[1.5rem]">
                        {thesis.keywords.length > 0 && (
                            <div className="flex flex-wrap gap-1">
                                {thesis.keywords.slice(0, 3).map((kw, i) => (
                                    <span
                                        key={i}
                                        className="inline-flex items-center gap-0.5 rounded-md bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground"
                                        data-skeleton-ignore
                                    >
                                        <Tag className="size-2.5" />
                                        {kw}
                                    </span>
                                ))}
                                {thesis.keywords.length > 3 && (
                                    <span className="rounded-md bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground">
                                        +{thesis.keywords.length - 3}
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
                            title={thesis.authorName}
                        >
                            <User className="size-3 text-muted-foreground" />
                            <span className="line-clamp-1">
                                {thesis.authorName}
                            </span>
                        </span>
                        <span className="flex items-center gap-1 text-[10px] text-muted-foreground">
                            <Hash className="size-2.5" />
                            NIM: {thesis.studentId}
                        </span>
                    </div>
                    <ChevronRight className="size-4 text-muted-foreground/50 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-primary" />
                </CardFooter>
            </Card>
        </div>
    );
}
