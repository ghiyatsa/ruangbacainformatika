import { Link } from '@inertiajs/react';
import { BookOpen } from 'lucide-react';
import BookController from '@/actions/App/Http/Controllers/BookController';
import { Badge } from '@/components/ui/badge';
import type { CatalogBook } from '@/components/welcome/types';

interface BookListItemProps {
    book: CatalogBook;
}

export default function BookListItem({ book }: BookListItemProps) {
    return (
        <Link
            href={BookController.show(book.slug)}
            className="group flex items-center gap-4 px-4 py-3 transition-colors hover:bg-muted/40 focus:bg-muted/40 focus:outline-none"
        >
            <div className="h-16 w-11 shrink-0 overflow-hidden rounded-md border bg-muted shadow-sm">
                <img
                    src={book.coverImageUrl}
                    alt={book.title}
                    className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
                />
            </div>

            <div className="flex min-w-0 flex-1 flex-col gap-0.5">
                <div className="flex flex-wrap items-center gap-1.5">
                    {book.categories.slice(0, 1).map((category) => (
                        <span
                            key={category}
                            className="rounded border px-1.5 py-px text-[10px] font-medium text-muted-foreground"
                        >
                            {category}
                        </span>
                    ))}
                    {book.isFeatured && (
                        <span className="rounded bg-primary/10 px-1.5 py-px text-[10px] font-semibold text-primary">
                            ✦ Unggulan
                        </span>
                    )}
                </div>
                <p className="line-clamp-1 text-sm leading-snug font-semibold transition-colors group-hover:text-primary">
                    {book.title}
                </p>
                <p className="line-clamp-1 text-xs text-muted-foreground">
                    {book.authors.join(', ') || 'Penulis anonim'}
                    {book.publishedYear && (
                        <span className="ml-2 text-[11px]">
                            · {book.publishedYear}
                        </span>
                    )}
                </p>
            </div>

            <div className="shrink-0">
                <Badge
                    variant={
                        !book.isAvailable
                            ? 'secondary'
                            : book.isBorrowable
                              ? 'default'
                              : 'secondary'
                    }
                    className="text-[10px] backdrop-blur-sm"
                >
                    {!book.isAvailable
                        ? 'Kosong'
                        : !book.isBorrowable
                          ? 'Referensi'
                          : 'Tersedia'}
                </Badge>
            </div>

            <div className="hidden shrink-0 items-center gap-1 text-[11px] text-muted-foreground sm:flex">
                <BookOpen className="size-3" />
                <span>{book.pages ? `${book.pages} hal` : '—'}</span>
            </div>
        </Link>
    );
}
