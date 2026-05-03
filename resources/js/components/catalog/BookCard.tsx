import { Link } from '@inertiajs/react';
import { BookOpen, Star } from 'lucide-react';
import BookController from '@/actions/App/Http/Controllers/BookController';
import { Badge } from '@/components/ui/badge';
import type { CatalogBook } from '@/components/welcome/types';

interface BookCardProps {
    book: CatalogBook;
}

export default function BookCard({ book }: BookCardProps) {
    // Reference books (isBorrowable=false) are always available in the library
    // since they cannot be borrowed — they stay on the shelf permanently.
    const availabilityStatus = !book.isBorrowable
        ? {
              label: 'Referensi',
              color: 'bg-amber-500/10 text-amber-700 dark:text-amber-400',
          }
        : book.isAvailable
          ? {
                label: 'Tersedia',
                color: 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400',
            }
          : { label: 'Kosong', color: 'bg-muted text-muted-foreground' };

    return (
        <Link
            href={BookController.show(book.slug)}
            className="group block rounded-2xl focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:outline-none"
        >
            <div className="relative flex h-full flex-col overflow-hidden rounded-2xl border bg-card transition-all duration-300 hover:shadow-lg hover:shadow-primary/5 dark:hover:shadow-primary/10">
                {/* Cover image area */}
                <div className="relative aspect-[3/4] overflow-hidden bg-muted">
                    <img
                        src={book.coverImageUrl}
                        alt={book.title}
                        className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                        loading="lazy"
                    />

                    {/* Gradient overlay on hover */}
                    <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100" />

                    {/* Top badges */}
                    <div className="absolute top-2 right-2 flex flex-col gap-1">
                        {book.isFeatured && (
                            <Badge className="gap-1 bg-primary/90 text-[10px] shadow-sm backdrop-blur-sm">
                                <Star className="size-2.5 fill-current" />
                                Unggulan
                            </Badge>
                        )}
                    </div>

                    {/* Bottom overlay (visible on hover) */}
                    <div className="absolute bottom-0 left-0 right-0 translate-y-full p-3 transition-transform duration-300 group-hover:translate-y-0">
                        <span className="inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-semibold text-gray-900 shadow-sm backdrop-blur-sm dark:bg-black/70 dark:text-white">
                            <BookOpen className="size-3" />
                            Lihat Detail
                        </span>
                    </div>
                </div>

                {/* Content area */}
                <div className="flex flex-1 flex-col gap-2 p-3 sm:p-4">
                    {/* Categories */}
                    <div className="flex flex-wrap gap-1">
                        {book.categories.slice(0, 2).map((c) => (
                            <span
                                key={c}
                                className="rounded-md bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground"
                            >
                                {c}
                            </span>
                        ))}
                    </div>

                    {/* Title */}
                    <h3 className="line-clamp-2 text-sm leading-snug font-bold transition-colors group-hover:text-primary">
                        {book.title}
                    </h3>

                    {/* Author */}
                    <p className="line-clamp-1 text-xs text-muted-foreground">
                        {book.authors.join(', ') || 'Penulis tidak tersedia'}
                    </p>

                    {/* Spacer to push footer down */}
                    <div className="flex-1" />

                    {/* Footer */}
                    <div className="flex items-center justify-between border-t pt-2.5">
                        <span
                            className={`rounded-full px-2 py-0.5 text-[10px] font-semibold ${availabilityStatus.color}`}
                        >
                            {availabilityStatus.label}
                        </span>
                        <div className="flex items-center gap-2 text-[11px] text-muted-foreground">
                            {book.publishedYear && (
                                <span>{book.publishedYear}</span>
                            )}
                            {book.pages && (
                                <>
                                    <span className="text-border">·</span>
                                    <span>{book.pages} hal</span>
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </Link>
    );
}
