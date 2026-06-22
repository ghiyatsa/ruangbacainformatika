import { Link } from '@inertiajs/react';
import {
    Bookmark,
    BookMarked,
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
import { useCatalogBookmarks } from '@/features/books/hooks/use-catalog-bookmarks';
import { instantLoadingPageProps } from '@/lib/inertia-loading';
import { cn } from '@/lib/utils';
import skripsiRoute from '@/routes/skripsi';
import thesisRoute from '@/routes/thesis';
import type { AcademicWorkData } from '@/features/academic-works/types';
import type { CatalogBookmarkRecord } from '@/features/books/hooks/use-catalog-bookmarks';

interface AcademicWorkCardProps {
    work: AcademicWorkData;
    workType: 'skripsi' | 'thesis';
}

export default function AcademicWorkCard({ work, workType }: AcademicWorkCardProps) {
    const { isBookmarked, toggleBookmark } = useCatalogBookmarks();
    const isBookmarkedByUser = isBookmarked({
        catalogType: workType,
        id: work.id,
    });

    const route = workType === 'skripsi' ? skripsiRoute : thesisRoute;
    const label = workType === 'skripsi' ? 'Skripsi' : 'Tesis';
    const Icon = workType === 'skripsi' ? BookMarked : GraduationCap;

    const bookmarkRecord: CatalogBookmarkRecord = {
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
    };

    return (
        <div className="group relative h-full">
            <Link
                href={route.show.url(work.studentId)}
                instant
                component={`${workType}/show`}
                pageProps={instantLoadingPageProps()}
                className="absolute inset-0 z-10 rounded-xl focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:outline-none"
                aria-label={`Lihat detail ${workType === 'skripsi' ? 'skripsi' : 'tesis'} ${work.title}`}
            />
            <Card className="relative flex h-full flex-col overflow-hidden transition-all duration-300 hover:border-primary/30">
                <CardHeader className="gap-3">
                    <div className="flex items-start justify-between gap-3">
                        <Badge
                            variant="secondary"
                            className="gap-1 bg-primary/10 text-xs text-primary hover:bg-primary/15"
                        >
                            <Icon className="size-2.5" />
                            {label}
                        </Badge>

                        <div className="flex items-center gap-2">
                            {work.year && (
                                <span className="flex items-center gap-1 text-[11px] text-muted-foreground">
                                    <Calendar className="size-3" />
                                    {work.year}
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
                            {work.title}
                        </p>
                    </div>
                </CardHeader>

                <CardContent className="flex flex-1 flex-col gap-3">
                    <div className="min-h-[1.5rem]">
                        {(() => {
                            const rawKeywords = work.keywords as any;
                            const keywordsList: string[] = Array.isArray(rawKeywords)
                                ? rawKeywords
                                : (typeof rawKeywords === 'string' && rawKeywords
                                    ? rawKeywords.split(',').map((s: string) => s.trim())
                                    : []);

                            if (keywordsList.length === 0) {
                                return null;
                            }

                            return (
                                <div className="flex flex-wrap gap-1">
                                    {keywordsList.slice(0, 3).map((kw: string, i: number) => (
                                        <span
                                            key={i}
                                            className="inline-flex items-center gap-0.5 rounded-md bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground"
                                            data-skeleton-ignore
                                        >
                                            <Tag className="size-2.5" />
                                            {kw}
                                        </span>
                                    ))}
                                    {keywordsList.length > 3 && (
                                        <span className="rounded-md bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground">
                                            +{keywordsList.length - 3}
                                        </span>
                                    )}
                                </div>
                            );
                        })()}
                    </div>
                </CardContent>

                <CardFooter className="mt-auto flex min-h-[4.5rem] items-center justify-between border-t">
                    <div className="flex flex-col gap-0.5">
                        <span
                            className="flex items-center gap-1 text-xs font-semibold text-foreground"
                            title={work.authorName}
                        >
                            <User className="size-3 text-muted-foreground" />
                            <span className="line-clamp-1">
                                {work.authorName}
                            </span>
                        </span>
                        <span className="flex items-center gap-1 text-[10px] text-muted-foreground">
                            <Hash className="size-2.5" />
                            NIM: {work.studentId}
                        </span>
                    </div>
                    <ChevronRight className="size-4 text-muted-foreground/50 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-primary" />
                </CardFooter>
            </Card>
        </div>
    );
}
