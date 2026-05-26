import { Link } from '@inertiajs/react';
import { BookOpen, Eye, Star } from 'lucide-react';
import BookController from '@/actions/App/Http/Controllers/BookController';
import { instantLoadingPageProps } from '@/lib/inertia-loading';
import type { CatalogBook } from '@/features/welcome/types';

interface BookListItemProps {
    book: CatalogBook;
}

export default function BookListItem({ book }: BookListItemProps) {
    const categories = Array.isArray(book.categories) ? book.categories : [];
    const visibleCategory = categories[0];
    const hiddenCategoriesCount = Math.max(categories.length - 1, 0);
    const authors = Array.isArray(book.authors) ? book.authors : [];

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
            instant
            component="books/show"
            pageProps={instantLoadingPageProps()}
            className="group flex items-center gap-4 px-4 py-3.5 transition-colors hover:bg-muted/40 focus:bg-muted/40 focus:outline-none sm:gap-5 sm:px-5 sm:py-4"
        >
            <div className="relative h-18 w-12 shrink-0 overflow-hidden rounded-lg border bg-muted shadow-sm sm:h-20 sm:w-14">
                <img
                    src={book.coverImageUrl}
                    alt={book.title}
                    className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
                    loading="lazy"
                />
                {book.isFeatured ? (
                    <div className="absolute top-0.5 right-0.5">
                        <Star className="size-3 fill-primary text-primary drop-shadow" />
                    </div>
                ) : null}
            </div>

            <div className="flex min-w-0 flex-1 flex-col gap-1">
                <div className="flex min-w-0 items-center gap-1.5">
                    {visibleCategory ? (
                        <span className="truncate rounded-md bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground">
                            {visibleCategory.name}
                        </span>
                    ) : null}
                    {hiddenCategoriesCount > 0 ? (
                        <span className="shrink-0 rounded-md bg-muted px-1.5 py-0.5 text-[10px] font-semibold text-muted-foreground">
                            +{hiddenCategoriesCount}
                        </span>
                    ) : null}
                </div>
                <p className="line-clamp-1 text-sm leading-snug font-semibold transition-colors group-hover:text-primary">
                    {book.title}
                </p>
                <p className="-mt-0.5 line-clamp-1 text-[11px] text-muted-foreground/80">
                    {book.shortDescription}
                </p>
                <p className="line-clamp-1 text-xs text-muted-foreground">
                    {authors.join(', ') || 'Penulis tidak tersedia'}
                    {book.publishedYear ? (
                        <span className="ml-2 text-[11px]">
                            &middot; {book.publishedYear}
                        </span>
                    ) : null}
                </p>
            </div>

            <div className="shrink-0">
                <span
                    className={`rounded-full px-2 py-0.5 text-[10px] font-semibold ${availabilityStatus.color}`}
                >
                    {availabilityStatus.label}
                </span>
            </div>

            <div className="hidden shrink-0 items-center gap-3 text-[11px] text-muted-foreground sm:flex">
                <div className="flex items-center gap-1">
                    <BookOpen className="size-3" />
                    <span>{book.pages ? `${book.pages} hal` : '-'}</span>
                </div>
                <div className="flex items-center gap-1">
                    <Eye className="size-3" />
                    <span>{book.viewCount.toLocaleString('id-ID')}</span>
                </div>
            </div>
        </Link>
    );
}
