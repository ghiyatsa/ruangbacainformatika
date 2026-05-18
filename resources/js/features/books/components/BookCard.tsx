import { Link } from '@inertiajs/react';
import { BookOpen, Eye, Star } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import BookController from '@/actions/App/Http/Controllers/BookController';
import { Badge } from '@/components/ui/badge';
import type { CatalogBook } from '@/features/welcome/types';
import { cn } from '@/lib/utils';

function CoverImage({
    src,
    alt,
    className,
}: {
    src: string;
    alt: string;
    className?: string;
}) {
    const [errored, setErrored] = useState(false);

    if (errored) {
        return (
            <div
                className={`flex items-center justify-center bg-muted ${className ?? ''}`}
            >
                <BookOpen className="size-10 text-muted-foreground/40" />
            </div>
        );
    }

    return (
        <img
            src={src}
            alt={alt}
            loading="lazy"
            className={className}
            onError={() => setErrored(true)}
        />
    );
}

function estimateCategoryBadgeWidth(name: string): number {
    return Math.min(Math.max(name.length * 6.4 + 18, 38), 180);
}

function CategoryBadges({
    categories,
}: {
    categories: NonNullable<CatalogBook['categories']>;
}) {
    const containerRef = useRef<HTMLDivElement | null>(null);
    const [containerWidth, setContainerWidth] = useState<number | null>(null);

    useEffect(() => {
        const container = containerRef.current;

        if (!container) {
            return;
        }

        const observer = new ResizeObserver(([entry]) => {
            const nextWidth = entry?.contentRect.width;

            if (nextWidth) {
                setContainerWidth(nextWidth);
            }
        });

        observer.observe(container);

        return () => observer.disconnect();
    }, []);

    const visibleCount = useMemo(() => {
        if (categories.length === 0) {
            return 0;
        }

        if (!containerWidth) {
            return categories.length;
        }

        const gapWidth = 4;
        const counterWidth = 32;
        let usedWidth = 0;
        let count = 0;

        for (const category of categories) {
            const nextWidth =
                estimateCategoryBadgeWidth(category.name) +
                (count > 0 ? gapWidth : 0);
            const hasHiddenAfterThis = count + 1 < categories.length;
            const reservedWidth = hasHiddenAfterThis
                ? counterWidth + gapWidth
                : 0;

            if (usedWidth + nextWidth + reservedWidth > containerWidth) {
                break;
            }

            usedWidth += nextWidth;
            count += 1;
        }

        return Math.max(count, 1);
    }, [categories, containerWidth]);

    const visibleCategories = categories.slice(0, visibleCount);
    const hiddenCategories = categories.slice(visibleCount);
    const hiddenCategoryNames = hiddenCategories
        .map((category) => category.name)
        .join(', ');

    return (
        <div
            ref={containerRef}
            className="flex min-h-4 min-w-0 items-center gap-1"
        >
            {visibleCategories.map((category) => (
                <span
                    key={category.slug}
                    className="max-w-44 truncate rounded-md bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground"
                >
                    {category.name}
                </span>
            ))}
            {hiddenCategories.length > 0 ? (
                <span
                    className="shrink-0 rounded-md bg-muted px-1.5 py-0.5 text-[10px] font-semibold text-muted-foreground"
                    title={hiddenCategoryNames}
                    aria-label={`${hiddenCategories.length} kategori lain: ${hiddenCategoryNames}`}
                >
                    +{hiddenCategories.length}
                </span>
            ) : null}
        </div>
    );
}

interface BookCardProps {
    book: CatalogBook;
    variant?: 'grid' | 'compact';
}

export default function BookCard({ book, variant = 'grid' }: BookCardProps) {
    const isCompact = variant === 'compact';
    const categories = Array.isArray(book.categories) ? book.categories : [];
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
            className="group block rounded-2xl focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:outline-none"
        >
            <div
                className={cn(
                    'relative flex h-full overflow-hidden rounded-2xl border bg-card transition-all duration-300 hover:shadow-lg hover:shadow-primary/5 dark:hover:shadow-primary/10',
                    !isCompact && 'sm:flex-col',
                )}
            >
                <div
                    className={cn(
                        'relative aspect-3/4 w-32 shrink-0 self-start overflow-hidden bg-muted',
                        isCompact ? 'sm:w-36' : 'sm:h-auto sm:w-full sm:self-auto',
                    )}
                >
                    <CoverImage
                        src={book.coverImageUrl}
                        alt={book.title}
                        className={cn(
                            'h-full w-full transition-transform duration-500 group-hover:scale-105',
                            isCompact
                                ? 'object-contain'
                                : 'object-contain sm:object-cover',
                        )}
                    />

                    <div className="absolute inset-0 hidden bg-linear-to-t from-black/60 via-transparent to-transparent opacity-0 transition-opacity duration-300 sm:block sm:group-hover:opacity-100" />

                    <div className="absolute top-2 left-2 inline-flex items-center gap-1 rounded-full bg-black/55 px-2 py-1 text-[10px] font-semibold text-white shadow-sm backdrop-blur-sm">
                        <Eye className="size-3" />
                        <span className="sm:inline">
                            {book.viewCount.toLocaleString('id-ID')}
                        </span>
                    </div>

                    <div className="absolute top-2 right-2 flex flex-col gap-1">
                        {book.isFeatured && (
                            <Badge className="gap-1 bg-primary/90 px-1.5 text-[10px] shadow-sm backdrop-blur-sm sm:px-2.5">
                                <Star className="size-2.5 fill-current" />
                                <span
                                    className={cn(
                                        isCompact
                                            ? 'hidden'
                                            : 'hidden sm:inline',
                                    )}
                                >
                                    Unggulan
                                </span>
                            </Badge>
                        )}
                    </div>

                    {!isCompact ? (
                        <div className="absolute right-0 bottom-0 left-0 hidden translate-y-full p-3 transition-transform duration-300 sm:block sm:group-hover:translate-y-0">
                            <span className="inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-semibold text-gray-900 shadow-sm backdrop-blur-sm dark:bg-black/70 dark:text-white">
                                <BookOpen className="size-3" />
                                Lihat Detail
                            </span>
                        </div>
                    ) : null}
                </div>

                <div className="flex min-w-0 flex-1 flex-col gap-1.5 p-3 sm:gap-2 sm:p-4">
                    <CategoryBadges categories={categories} />

                    <div className="min-h-[2.5rem]">
                        <h3 className="line-clamp-2 text-sm leading-snug font-bold transition-colors group-hover:text-primary sm:text-sm">
                            {book.title}
                        </h3>
                    </div>

                    <div
                        className={cn(
                            'min-h-[0.95rem]',
                            !isCompact && 'sm:min-h-[1.9rem]',
                        )}
                    >
                        <p
                            className={cn(
                                'line-clamp-1 text-[11px] leading-relaxed text-muted-foreground/80 sm:-mt-1',
                                !isCompact && 'sm:line-clamp-2',
                            )}
                        >
                            {book.shortDescription}
                        </p>
                    </div>

                    <div className="min-h-4">
                        <p className="line-clamp-1 text-xs text-muted-foreground">
                            {authors.join(', ') || 'Penulis tidak tersedia'}
                        </p>
                    </div>

                    <div className="hidden flex-1 sm:block" />

                    <div className="mt-auto flex min-h-[1.9rem] items-center justify-between gap-2 border-t pt-2 sm:min-h-8 sm:pt-2.5">
                        <span
                            className={`shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold ${availabilityStatus.color}`}
                        >
                            {availabilityStatus.label}
                        </span>
                        <div className="flex min-w-0 items-center gap-1.5 truncate text-[11px] text-muted-foreground sm:gap-2">
                            {book.publishedYear ? (
                                <span>{book.publishedYear}</span>
                            ) : null}
                            {book.pages ? (
                                <>
                                    <span className="text-border">
                                        &middot;
                                    </span>
                                    <span>{book.pages} hal</span>
                                </>
                            ) : null}
                        </div>
                    </div>
                </div>
            </div>
        </Link>
    );
}
