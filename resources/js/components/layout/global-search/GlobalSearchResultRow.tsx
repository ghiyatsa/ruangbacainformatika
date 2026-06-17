import { BookOpen, ClipboardCheck, FilePenLine, GraduationCap, Search } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import type { SearchListItem } from '@/components/layout/global-search/types';

interface GlobalSearchResultRowProps {
    item: SearchListItem;
    isSelected: boolean;
}

export function GlobalSearchResultRow({
    item,
    isSelected,
}: GlobalSearchResultRowProps) {
    return (
        <div
            className={cn(
                'flex items-center gap-3 rounded-lg px-3 py-3 transition-colors',
                isSelected
                    ? 'bg-accent text-accent-foreground'
                    : 'hover:bg-accent/50',
            )}
        >
            {item.itemType === 'book' ? (
                <>
                    <div className="aspect-2/3 w-9 shrink-0 overflow-hidden rounded-sm border bg-muted shadow-sm">
                        <img
                            src={item.coverImageUrl}
                            alt=""
                            width={36}
                            height={54}
                            className="h-full w-full object-cover"
                        />
                    </div>
                    <div className="flex flex-1 flex-col gap-0.5">
                        <div className="flex items-center gap-2">
                            <span className="line-clamp-1 font-semibold tracking-tight">
                                {item.title}
                            </span>
                            <Badge
                                variant="outline"
                                className="h-4 px-1 text-[9px] uppercase"
                            >
                                Buku
                            </Badge>
                        </div>
                        <span className="line-clamp-1 text-xs text-muted-foreground">
                            {item.authors?.join(', ')}
                        </span>
                    </div>
                    <BookOpen className="ml-auto size-4 text-muted-foreground" />
                </>
            ) : item.itemType === 'post' ? (
                <>
                    <div className="aspect-2/3 w-9 shrink-0 overflow-hidden rounded-sm border bg-muted shadow-sm">
                        <img
                            src={item.coverImageUrl}
                            alt=""
                            width={36}
                            height={54}
                            className="h-full w-full object-cover"
                        />
                    </div>
                    <div className="flex flex-1 flex-col gap-0.5">
                        <div className="flex items-center gap-2">
                            <span className="line-clamp-1 font-semibold tracking-tight">
                                {item.title}
                            </span>
                            <Badge
                                variant="outline"
                                className="h-4 px-1 text-[9px] uppercase"
                            >
                                Artikel
                            </Badge>
                        </div>
                        <span className="line-clamp-1 text-xs text-muted-foreground">
                            {item.authorName}
                        </span>
                    </div>
                    <FilePenLine className="ml-auto size-4 text-muted-foreground" />
                </>
            ) : item.itemType === 'skripsi' ? (
                <>
                    <div className="flex size-9 shrink-0 items-center justify-center rounded-sm border bg-muted shadow-sm">
                        <GraduationCap className="size-5 text-muted-foreground" />
                    </div>
                    <div className="flex flex-1 flex-col gap-0.5">
                        <div className="flex items-center gap-2">
                            <span className="line-clamp-1 font-semibold tracking-tight">
                                {item.title}
                            </span>
                            <Badge
                                variant="outline"
                                className="h-4 px-1 text-[9px] uppercase"
                            >
                                Skripsi
                            </Badge>
                        </div>
                        <span className="line-clamp-1 text-xs text-muted-foreground">
                            {item.authorName} - {item.studentId}
                        </span>
                    </div>
                    <Search className="ml-auto size-4 text-muted-foreground" />
                </>
            ) : item.itemType === 'thesis' ? (
                <>
                    <div className="flex size-9 shrink-0 items-center justify-center rounded-sm border bg-muted shadow-sm">
                        <GraduationCap className="size-5 text-muted-foreground" />
                    </div>
                    <div className="flex flex-1 flex-col gap-0.5">
                        <div className="flex items-center gap-2">
                            <span className="line-clamp-1 font-semibold tracking-tight">
                                {item.title}
                            </span>
                            <Badge
                                variant="outline"
                                className="h-4 px-1 text-[9px] uppercase"
                            >
                                Tesis
                            </Badge>
                        </div>
                        <span className="line-clamp-1 text-xs text-muted-foreground">
                            {item.authorName} - {item.studentId}
                        </span>
                    </div>
                    <Search className="ml-auto size-4 text-muted-foreground" />
                </>
            ) : (
                <>
                    <div className="flex size-9 shrink-0 items-center justify-center rounded-sm border bg-muted shadow-sm">
                        <ClipboardCheck className="size-5 text-muted-foreground" />
                    </div>
                    <div className="flex flex-1 flex-col gap-0.5">
                        <div className="flex items-center gap-2">
                            <span className="line-clamp-1 font-semibold tracking-tight">
                                {item.title}
                            </span>
                            <Badge
                                variant="outline"
                                className="h-4 px-1 text-[9px] uppercase"
                            >
                                Laporan KP
                            </Badge>
                        </div>
                        <span className="line-clamp-1 text-xs text-muted-foreground">
                            {item.authorName} - {item.studentId}
                        </span>
                    </div>
                    <Search className="ml-auto size-4 text-muted-foreground" />
                </>
            )}
        </div>
    );
}
