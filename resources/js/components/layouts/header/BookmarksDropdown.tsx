import { Link } from '@inertiajs/react';
import { Bookmark, Trash2 } from 'lucide-react';
import * as React from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogDescription,
    DialogContent,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useCatalogBookmarks } from '@/hooks/use-catalog-bookmarks';
import { useIsMobile } from '@/hooks/use-mobile';

export function BookmarksDropdown() {
    const { bookmarks, bookmarkedCount, clearBookmarks, removeBookmark } =
        useCatalogBookmarks();
    const isMobile = useIsMobile();
    const [open, setOpen] = React.useState(false);

    const handleOpenChange = React.useEffectEvent((nextOpen: boolean) => {
        setOpen(nextOpen);
    });

    const trigger = (
        <Button
            variant="ghost"
            size="icon"
            className="group relative h-10 w-10 rounded-xl transition-all duration-300 hover:scale-105 active:scale-95 sm:h-9 sm:w-9"
            aria-label={`Bookmark, ${bookmarkedCount} tersimpan`}
            title="Bookmark"
        >
            <Bookmark className="size-[18px] text-primary transition-transform duration-300 group-hover:scale-110 sm:h-4.5 sm:w-4.5" />
            <span className="sr-only">Bookmark</span>
            {bookmarkedCount > 0 && (
                <Badge className="absolute top-0.5 right-0.5 flex h-3 min-w-3 animate-in items-center justify-center rounded-full px-1 py-0 text-[8px] leading-none shadow-sm duration-200 zoom-in-50">
                    {bookmarkedCount > 9 ? '9+' : bookmarkedCount}
                </Badge>
            )}
        </Button>
    );

    const mobileHeader = (
        <>
            <div className="flex items-center justify-between border-b border-border/60 px-4 py-3">
                <div>
                    <DialogTitle className="text-sm font-semibold">
                        Bookmark
                    </DialogTitle>
                    <DialogDescription className="sr-only">
                        Daftar katalog yang sudah kamu simpan.
                    </DialogDescription>
                </div>

                {bookmarkedCount > 0 && (
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        className="h-8 rounded-lg px-2 text-xs"
                        onClick={clearBookmarks}
                    >
                        Hapus semua
                    </Button>
                )}
            </div>
        </>
    );

    const desktopHeader = (
        <div className="flex items-center justify-between border-b border-border/60 px-4 py-3">
            <div>
                <h2 className="text-sm font-semibold">Bookmark</h2>
            </div>

            {bookmarkedCount > 0 && (
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    className="h-8 rounded-lg px-2 text-xs"
                    onClick={clearBookmarks}
                >
                    Hapus semua
                </Button>
            )}
        </div>
    );

    const contentBody = (
        <>
            <div
                className={`max-h-96 overflow-y-auto ${open ? 'motion-safe:animate-in motion-safe:duration-200 motion-safe:fade-in-0 motion-safe:slide-in-from-top-1' : ''}`}
            >
                {bookmarkedCount > 0 ? (
                    <div className="space-y-2 p-2">
                        {bookmarks.map((bookmark, index) => (
                            <div
                                key={`${bookmark.catalogType}:${bookmark.id}`}
                                className={`flex items-center gap-2.5 rounded-2xl px-3 py-3 transition-[background-color,transform,opacity] duration-200 hover:bg-accent/60 ${open ? 'motion-safe:animate-in motion-safe:fade-in-0 motion-safe:slide-in-from-top-1' : ''}`}
                                style={
                                    open
                                        ? {
                                              animationDuration: '220ms',
                                              animationDelay: `${Math.min(index * 35, 140)}ms`,
                                              animationFillMode: 'backwards',
                                          }
                                        : undefined
                                }
                            >
                                {bookmark.coverImageUrl ? (
                                    <div className="flex h-18 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-muted">
                                        <img
                                            src={bookmark.coverImageUrl}
                                            alt={bookmark.title}
                                            className="h-full w-full object-cover"
                                        />
                                    </div>
                                ) : (
                                    <div className="flex h-18 w-12 shrink-0 items-center justify-center rounded-lg bg-primary/8 text-primary">
                                        <Bookmark className="size-4" />
                                    </div>
                                )}

                                <div className="min-w-0 flex-1 space-y-1.5">
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="min-w-0 space-y-1">
                                            <Link
                                                href={bookmark.href}
                                                className="line-clamp-2 text-sm font-semibold text-foreground hover:text-primary"
                                            >
                                                {bookmark.title}
                                            </Link>
                                            <p className="line-clamp-1 text-xs text-muted-foreground">
                                                {bookmark.subtitle ??
                                                    'Detail tidak tersedia'}
                                            </p>
                                        </div>

                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon-sm"
                                            className="shrink-0 rounded-full text-muted-foreground hover:text-destructive"
                                            aria-label={`Hapus ${bookmark.title} dari bookmark`}
                                            onClick={() =>
                                                removeBookmark(bookmark)
                                            }
                                        >
                                            <Trash2 className="size-4" />
                                        </Button>
                                    </div>

                                    <div className="flex flex-wrap items-center gap-1.5">
                                        <Badge
                                            variant="secondary"
                                            className="rounded-full px-2 py-0.5"
                                        >
                                            {bookmark.kindLabel}
                                        </Badge>
                                        {bookmark.catalogType !== 'book' &&
                                        bookmark.statusLabel ? (
                                            <Badge
                                                variant="outline"
                                                className="rounded-full px-2 py-0.5"
                                            >
                                                {bookmark.statusLabel}
                                            </Badge>
                                        ) : null}
                                        {bookmark.year ? (
                                            <span className="text-xs text-muted-foreground">
                                                {bookmark.year}
                                            </span>
                                        ) : null}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="px-4 py-8 text-center motion-safe:animate-in motion-safe:duration-200 motion-safe:fade-in-0 motion-safe:zoom-in-95">
                        <div className="mx-auto mb-3 flex size-11 items-center justify-center rounded-full bg-primary/8 text-primary">
                            <Bookmark className="size-5" />
                        </div>
                        <p className="text-sm font-medium">
                            Belum ada bookmark
                        </p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Simpan katalog yang ingin kamu buka lagi nanti.
                        </p>
                    </div>
                )}
            </div>
        </>
    );

    if (isMobile) {
        return (
            <Dialog open={open} onOpenChange={handleOpenChange}>
                <DialogTrigger asChild>{trigger}</DialogTrigger>

                <DialogContent
                    className="w-[min(92vw,22rem)] max-w-[min(92vw,22rem)] gap-0 overflow-hidden p-0"
                    showCloseButton={false}
                >
                    {mobileHeader}
                    {contentBody}
                </DialogContent>
            </Dialog>
        );
    }

    return (
        <DropdownMenu open={open} onOpenChange={handleOpenChange}>
            <DropdownMenuTrigger asChild>{trigger}</DropdownMenuTrigger>

            <DropdownMenuContent align="end" className="w-88 min-w-88 p-0">
                {desktopHeader}
                {contentBody}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
