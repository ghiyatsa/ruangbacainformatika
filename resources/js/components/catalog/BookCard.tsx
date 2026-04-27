import { Link } from '@inertiajs/react';
import { BookOpen } from 'lucide-react';
import BookController from '@/actions/App/Http/Controllers/BookController';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import type { CatalogBook } from '@/components/welcome/types';

interface BookCardProps {
    book: CatalogBook;
}

export default function BookCard({ book }: BookCardProps) {
    return (
        <Card className="group overflow-hidden transition-all hover:shadow-md">
            <Link
                href={BookController.show(book.slug)}
                className="block focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:outline-none"
            >
                <CardContent className="p-0">
                    <div className="relative aspect-3/4 overflow-hidden bg-muted">
                        <img
                            src={book.coverImageUrl}
                            alt={book.title}
                            className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                        />
                        <div className="absolute top-2 right-2 flex flex-col gap-1">
                            {book.isFeatured && (
                                <Badge className="bg-primary/90 text-[10px] backdrop-blur-sm">
                                    ✦ Unggulan
                                </Badge>
                            )}
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
                    </div>
                    <div className="p-4">
                        <div className="mb-2 flex flex-wrap gap-1">
                            {book.categories.slice(0, 2).map((c) => (
                                <span
                                    key={c}
                                    className="rounded border px-1.5 py-px text-[10px] font-medium text-muted-foreground"
                                >
                                    {c}
                                </span>
                            ))}
                        </div>
                        <h3 className="line-clamp-1 text-sm font-bold transition-colors group-hover:text-primary">
                            {book.title}
                        </h3>
                        <p className="mt-0.5 line-clamp-1 text-xs text-muted-foreground">
                            {book.authors.join(', ') || 'Penulis anonim'}
                        </p>
                        <div className="mt-3 flex items-center justify-between border-t pt-3 text-[11px] text-muted-foreground">
                            <div className="flex items-center gap-1">
                                <BookOpen className="size-3" />
                                <span>
                                    {book.pages ? `${book.pages} hal` : '—'}
                                </span>
                            </div>
                            {book.publishedYear && (
                                <span>{book.publishedYear}</span>
                            )}
                        </div>
                    </div>
                </CardContent>
            </Link>
        </Card>
    );
}
