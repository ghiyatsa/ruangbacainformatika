import { AutoSkeleton } from 'auto-skeleton-react';
import { BookOpen } from 'lucide-react';

/**
 * Skeleton loader for BookListItem — uses auto-skeleton-react to
 * mirror the real list item's DOM structure automatically.
 */
export default function BookListItemSkeleton() {
    return (
        <AutoSkeleton
            loading={true}
            config={{
                animation: 'pulse',
                borderRadius: 6,
            }}
        >
            <div className="flex items-center gap-4 px-4 py-3.5 sm:gap-5 sm:px-5 sm:py-4">
                {/* Thumbnail placeholder */}
                <div className="h-18 w-12 shrink-0 overflow-hidden rounded-lg border bg-muted sm:h-20 sm:w-14">
                    <img
                        src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='1' height='1'%3E%3C/svg%3E"
                        alt=""
                        className="h-full w-full object-cover"
                        data-skeleton-role="image"
                    />
                </div>

                {/* Content placeholder */}
                <div className="flex min-w-0 flex-1 flex-col gap-1">
                    <div className="flex items-center gap-1.5">
                        <span className="rounded-md bg-muted px-1.5 py-0.5 text-[10px]">
                            Kategori
                        </span>
                    </div>
                    <p className="text-sm leading-snug font-semibold">
                        Judul buku placeholder
                    </p>
                    <p className="text-xs text-muted-foreground">
                        Nama penulis · 2024
                    </p>
                </div>

                {/* Status placeholder */}
                <div className="shrink-0">
                    <span className="rounded-full px-2 py-0.5 text-[10px]">
                        Tersedia
                    </span>
                </div>

                {/* Page count — desktop */}
                <div className="hidden shrink-0 items-center gap-1 text-[11px] sm:flex">
                    <BookOpen className="size-3" />
                    <span>200 hal</span>
                </div>
            </div>
        </AutoSkeleton>
    );
}
