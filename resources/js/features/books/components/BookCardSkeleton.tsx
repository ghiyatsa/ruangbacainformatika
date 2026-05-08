import { AutoSkeleton } from 'auto-skeleton-react';

/**
 * Skeleton loader for BookCard — uses auto-skeleton-react to
 * mirror the real card's DOM structure automatically.
 */
export default function BookCardSkeleton() {
    return (
        <AutoSkeleton
            loading={true}
            config={{
                animation: 'pulse',
                borderRadius: 8,
            }}
        >
            <div className="flex h-full flex-col overflow-hidden rounded-2xl border bg-card">
                {/* Cover placeholder */}
                <div className="relative aspect-3/4 overflow-hidden bg-muted">
                    <img
                        src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='1' height='1'%3E%3C/svg%3E"
                        alt=""
                        className="h-full w-full object-cover"
                        data-skeleton-role="image"
                    />
                </div>

                {/* Content placeholder */}
                <div className="flex flex-1 flex-col gap-2 p-3 sm:p-4">
                    <div className="flex gap-1">
                        <span className="rounded-md bg-muted px-1.5 py-0.5 text-[10px]">
                            Kategori
                        </span>
                    </div>
                    <h3 className="text-sm leading-snug font-bold">
                        Judul buku placeholder teks
                    </h3>
                    <p className="text-xs text-muted-foreground">
                        Nama penulis buku
                    </p>
                    <div className="flex-1" />
                    <div className="flex items-center justify-between border-t pt-2.5">
                        <span className="rounded-full px-2 py-0.5 text-[10px]">
                            Tersedia
                        </span>
                        <div className="flex items-center gap-2 text-[11px]">
                            <span>2024</span>
                            <span>·</span>
                            <span>200 hal</span>
                        </div>
                    </div>
                </div>
            </div>
        </AutoSkeleton>
    );
}
