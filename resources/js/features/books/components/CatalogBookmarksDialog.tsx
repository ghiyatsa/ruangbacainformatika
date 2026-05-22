import { Link } from '@inertiajs/react';
import { Bookmark, Trash2, X } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { useCatalogBookmarks } from '@/hooks/use-catalog-bookmarks';
import type { ReactNode } from 'react';

interface CatalogBookmarksDialogProps {
    trigger: ReactNode;
    onOpenChange?: (open: boolean) => void;
    open?: boolean;
}

export function CatalogBookmarksDialog({
    trigger,
    onOpenChange,
    open,
}: CatalogBookmarksDialogProps) {
    const { bookmarks, bookmarkedCount, clearBookmarks, removeBookmark } =
        useCatalogBookmarks();

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent
                showCloseButton={false}
                className="max-h-[85vh] overflow-hidden sm:max-w-2xl"
            >
                <DialogHeader className="space-y-3 text-left">
                    <div className="flex items-start justify-between gap-3">
                        <div className="space-y-1">
                            <DialogTitle className="flex items-center gap-2">
                                <Bookmark className="size-4 text-primary" />
                                Bookmark
                            </DialogTitle>
                            <DialogDescription>
                                Simpan pilihan katalog untuk dibuka kembali.
                            </DialogDescription>
                        </div>

                        <div className="flex items-center gap-2">
                            {bookmarkedCount > 0 ? (
                                <Badge
                                    variant="secondary"
                                    className="shrink-0 rounded-full px-2.5 py-1"
                                >
                                    {bookmarkedCount} item
                                </Badge>
                            ) : null}
                            <DialogClose asChild>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon-sm"
                                    className="shrink-0 rounded-full text-muted-foreground hover:text-foreground"
                                    aria-label="Tutup dialog"
                                >
                                    <X className="size-4" />
                                </Button>
                            </DialogClose>
                        </div>
                    </div>
                </DialogHeader>

                {bookmarkedCount > 0 ? (
                    <div className="space-y-3">
                        <div className="max-h-[55vh] space-y-2.5 overflow-y-auto pr-1">
                            {bookmarks.map((bookmark) => {
                                return (
                                    <div
                                        key={`${bookmark.catalogType}:${bookmark.id}`}
                                        className="flex items-center gap-2.5 rounded-xl border bg-card/70 p-2.5"
                                    >
                                        {bookmark.coverImageUrl ? (
                                            <div className="flex h-20 w-14 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-muted">
                                                <img
                                                    src={bookmark.coverImageUrl}
                                                    alt={bookmark.title}
                                                    className="h-full w-full object-cover"
                                                />
                                            </div>
                                        ) : null}

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
                                                            'Detail penulis tidak tersedia'}
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
                                                {bookmark.catalogType !==
                                                    'book' &&
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
                                                {bookmark.meta ? (
                                                    <span className="line-clamp-1 text-xs text-muted-foreground">
                                                        {bookmark.meta}
                                                    </span>
                                                ) : null}
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>

                        <div className="flex items-center justify-between gap-3 border-t pt-4">
                            <p className="text-xs leading-5 text-muted-foreground">
                                Tersimpan di browser ini.
                            </p>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                className="rounded-xl"
                                onClick={clearBookmarks}
                            >
                                Hapus semua
                            </Button>
                        </div>
                    </div>
                ) : (
                    <div className="rounded-2xl border border-dashed bg-muted/25 px-6 py-12 text-center">
                        <Bookmark className="mx-auto mb-3 size-10 text-muted-foreground/40" />
                        <h3 className="text-sm font-semibold text-foreground">
                            Belum ada bookmark
                        </h3>
                        <p className="mt-2 text-sm leading-6 text-muted-foreground">
                            Simpan katalog yang ingin kamu buka lagi nanti.
                        </p>
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
}
