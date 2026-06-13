import { Deferred, Form, Link, usePage } from '@inertiajs/react';
import {
    Bookmark,
    BookOpen,
    Building2,
    Calendar,
    CheckCircle2,
    Eye,
    FileText,
    Globe,
    Hash,
    Library,
    MapPinned,
    ShoppingCart,
    Star,
    XCircle,
} from 'lucide-react';
import { useState } from 'react';

import LoanRequestController from '@/actions/App/Http/Controllers/LoanRequestController';
import { Breadcrumbs } from '@/components/common/Breadcrumbs';
import { KtiDetailItem } from '@/components/kti/KtiDetailItem';
import { KtiDetailPage } from '@/components/kti/KtiDetailPage';
import { KtiRelatedSection } from '@/components/kti/KtiRelatedSection';
import { KtiShareButton } from '@/components/kti/KtiShareButton';
import { DeferredGlobalContentNotice } from '@/components/layout/GlobalContentNotice';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import BookCard from '@/features/books/components/BookCard';
import BookCardSkeleton from '@/features/books/components/BookCardSkeleton';
import { useCatalogBookmarks } from '@/features/books/hooks/use-catalog-bookmarks';
import DeferredCatalogRescue from '@/features/welcome/components/DeferredCatalogRescue';
import { cn } from '@/lib/utils';
import booksRoute from '@/routes/books';

import type { CatalogBookmarkRecord } from '@/features/books/hooks/use-catalog-bookmarks';
import type { BookData, LoanRequestSummary } from '@/features/books/types';
import type { Auth, LoanRequestCart } from '@/types';

export interface BookDetailPageProps {
    book?: { data: BookData };
    loanRequest?: LoanRequestSummary | null;
    relatedBooks?: BookData[];
    loading?: boolean;
}



function BookDescriptionSkeleton() {
    return (
        <div className="space-y-6">
            <div className="space-y-3">
                <Skeleton className="h-4 w-full" />
                <Skeleton className="h-4 w-11/12" />
                <Skeleton className="h-4 w-10/12" />
                <Skeleton className="h-4 w-4/5" />
            </div>
            <div className="space-y-3">
                <Skeleton className="h-4 w-full" />
                <Skeleton className="h-4 w-full" />
                <Skeleton className="h-4 w-5/6" />
                <Skeleton className="h-4 w-2/3" />
            </div>
        </div>
    );
}

export default function BookDetailPage(props: BookDetailPageProps) {
    const { auth, loanRequestCart } = usePage<{
        auth: Auth;
        loanRequestCart: LoanRequestCart | null;
    }>().props;
    const user = auth.user;
    const { isBookmarked, toggleBookmark } = useCatalogBookmarks();
    const [imageLoaded, setImageLoaded] = useState(false);
    const book = props.book?.data ?? null;
    const loanRequest = props.loanRequest ?? null;
    const requestSummary = loanRequest ?? {
        count: 0,
        maxBooks: 0,
        activeLoansCount: 0,
        containsBook: false,
        hasActiveQr: false,
    };
    const isBookmarkedByUser = book
        ? isBookmarked({
              catalogType: 'book',
              id: book.id,
          })
        : false;
    const bookmarkRecord: CatalogBookmarkRecord | null = book
        ? {
              catalogType: 'book',
              id: book.id,
              href: booksRoute.show.url(book.slug),
              title: book.title,
              subtitle: book.authors.join(', ') || 'Penulis tidak tersedia',
              meta: book.pages ? `${book.pages} halaman` : null,
              year: book.publishedYear ?? null,
              coverImageUrl: book.coverImageUrl,
              kindLabel: 'Buku',
              statusLabel: !book.isBorrowable
                  ? 'Referensi'
                  : book.isAvailable
                    ? 'Siap dipinjam'
                    : 'Sedang kosong',
          }
        : null;

    const availabilityColor = !book?.isBorrowable
        ? 'text-amber-600 dark:text-amber-400'
        : book?.isAvailable
          ? 'text-emerald-600 dark:text-emerald-400'
          : 'text-red-600 dark:text-red-400';

    const availabilityLabel = !book?.isBorrowable
        ? 'Baca di tempat'
        : book?.isAvailable
          ? 'Tersedia'
          : 'Tidak Tersedia';

    const AvailabilityIcon =
        !book?.isBorrowable || book?.isAvailable ? CheckCircle2 : XCircle;

    const availabilityBackground = !book?.isBorrowable
        ? 'bg-amber-500/10 border-amber-500/20'
        : book?.isAvailable
          ? 'bg-emerald-500/10 border-emerald-500/20'
          : 'bg-red-500/10 border-red-500/20';
    const seoKeywords = book
        ? [
              book.title,
              ...book.authors,
              ...book.categories.map((category) => category.name),
              'katalog buku',
              'ruang baca informatika',
          ].filter((value): value is string => Boolean(value))
        : ['katalog buku', 'ruang baca informatika'];
    const shelfLocations = book?.displayShelfLocations.join(', ') ?? '';
    const authorsData = book?.authorsData;
    const publisherData = book?.publisherData;

    return (
        <KtiDetailPage
            title={book?.title ?? 'Detail Buku'}
            description={
                book?.description
                    ? book.description.slice(0, 160)
                    : book
                      ? `${book.title} tersedia di Ruang Baca Teknik Informatika Universitas Malikussaleh.`
                      : 'Memuat detail buku dari katalog Ruang Baca Teknik Informatika Universitas Malikussaleh.'
            }
            image={book?.coverImageUrl}
            keywords={seoKeywords}
            showBackground={false}
            deferSecondaryContent
            contentClassName="pt-2 pb-10 sm:pt-3"
            hero={
                <div className="relative -mt-20 overflow-hidden sm:-mt-28 md:-mt-24">
                    <div
                        className="pointer-events-none absolute inset-0"
                        aria-hidden="true"
                    >
                        <div className="absolute top-[8%] left-[10%] h-40 w-40 rounded-full bg-primary/12 blur-3xl" />
                        <div className="absolute right-[8%] bottom-[12%] h-56 w-56 rounded-full bg-primary/10 blur-3xl" />
                    </div>
                    <div className="absolute inset-0 bg-linear-to-b from-background/30 via-background/60 to-background" />

                    <div className="relative mx-auto max-w-7xl px-4 pt-24 pb-6 sm:px-6 sm:pt-30 sm:pb-8 lg:px-8">
                        <DeferredGlobalContentNotice className="hidden md:block" />
                        <div className="hidden sm:mb-6 sm:block">
                            <Breadcrumbs
                                breadcrumbs={[
                                    { title: 'Beranda', href: '/' },
                                    {
                                        title: 'Buku',
                                        href: booksRoute.index.url(),
                                    },
                                    {
                                        title: book?.title ?? (
                                            <Skeleton className="h-4 w-28" />
                                        ),
                                        href: book
                                            ? booksRoute.show.url(book.slug)
                                            : booksRoute.index.url(),
                                    },
                                ]}
                            />
                        </div>

                        <div className="grid items-center gap-8 md:grid-cols-12 md:gap-8">
                            <div className="md:col-span-3">
                                <div className="flex w-full items-center justify-center">
                                    <div className="relative flex aspect-[3/4] w-full max-w-[16rem] items-center justify-center overflow-hidden rounded-2xl border bg-muted/10 shadow-xs sm:max-w-none sm:h-96 sm:w-72 md:h-[22rem] md:w-[16.5rem]">
                                        {book && (
                                            <>
                                                <img
                                                    src={book.coverImageUrl}
                                                    alt={`Cover buku ${book.title}`}
                                                    fetchPriority="high"
                                                    decoding="async"
                                                    loading="lazy"
                                                    width={256}
                                                    height={341}
                                                    sizes="100vw"
                                                    onLoad={() => setImageLoaded(true)}
                                                    className={cn(
                                                        "block h-auto w-full object-contain sm:hidden transition-opacity duration-300",
                                                        imageLoaded ? "opacity-100" : "opacity-0 absolute"
                                                    )}
                                                />

                                                <Dialog>
                                                    <DialogTrigger asChild>
                                                        <button
                                                            type="button"
                                                            className={cn(
                                                                "bg-transparent text-left transition duration-200 hover:scale-[1.015] focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:outline-none sm:h-96 sm:w-auto sm:items-center sm:justify-center md:h-[22rem]",
                                                                imageLoaded ? "sm:flex" : "hidden"
                                                            )}
                                                            aria-label={`Lihat cover penuh buku ${book.title}`}
                                                        >
                                                            <img
                                                                src={book.coverImageUrl}
                                                                alt={`Cover buku ${book.title}`}
                                                                fetchPriority="high"
                                                                decoding="async"
                                                                loading="lazy"
                                                                width={288}
                                                                height={384}
                                                                sizes="(min-width: 1024px) 20rem, (min-width: 768px) 28vw, 65vw"
                                                                onLoad={() => setImageLoaded(true)}
                                                                className="block h-full max-h-[28rem] w-auto max-w-full object-contain transition duration-300"
                                                            />
                                                        </button>
                                                    </DialogTrigger>

                                                    <DialogContent
                                                        className="max-w-[calc(100vw-2rem)] gap-0 bg-background/90 p-2 shadow-xl ring-0 backdrop-blur-sm sm:max-w-md"
                                                        overlayClassName="bg-black/45"
                                                    >
                                                        <DialogTitle className="sr-only">
                                                            Cover buku {book.title}
                                                        </DialogTitle>
                                                        <DialogDescription className="sr-only">
                                                            Pratinjau cover buku.
                                                        </DialogDescription>
                                                        <div className="flex max-h-[80vh] items-center justify-center overflow-hidden">
                                                            <img
                                                                src={book.coverImageUrl}
                                                                alt={`Cover penuh buku ${book.title}`}
                                                                width={448}
                                                                height={600}
                                                                className="max-h-[80vh] w-full object-contain"
                                                            />
                                                        </div>
                                                    </DialogContent>
                                                </Dialog>
                                            </>
                                        )}

                                        {(!book || !imageLoaded) && (
                                            <Skeleton className="absolute inset-0 h-full w-full animate-pulse rounded-2xl" />
                                        )}
                                    </div>
                                </div>
                            </div>

                            <div className="flex flex-col justify-center md:col-span-9">
                                <div className="mb-3 flex flex-wrap gap-1.5">
                                    {book ? (
                                        <>
                                            {book.isFeatured ? (
                                                <Badge className="gap-1 border-primary/20 bg-primary/15 text-primary hover:bg-primary/20">
                                                    <Star className="size-3 fill-current" />
                                                    Unggulan
                                                </Badge>
                                            ) : null}

                                            {book.categories.map(
                                                (category, index) => (
                                                    <Link
                                                        key={
                                                            category.slug ??
                                                            `${category.name}-${index}`
                                                        }
                                                        href={booksRoute.index.url({
                                                            query: {
                                                                category:
                                                                    category.slug,
                                                            },
                                                        })}
                                                    >
                                                        <Badge
                                                            variant="secondary"
                                                            className="bg-muted"
                                                        >
                                                            {category.name}
                                                        </Badge>
                                                    </Link>
                                                ),
                                            )}
                                        </>
                                    ) : (
                                        <>
                                            <Skeleton className="h-6 w-20 rounded-full" />
                                            <Skeleton className="h-6 w-24 rounded-full" />
                                            <Skeleton className="h-6 w-18 rounded-full" />
                                        </>
                                    )}
                                </div>

                                <h1 className="mb-3 text-3xl leading-tight font-bold tracking-tight sm:text-4xl lg:text-5xl">
                                    {book ? (
                                        book.title
                                    ) : (
                                        <div className="space-y-2 max-w-3xl">
                                            <Skeleton className="h-8 w-11/12 sm:h-10 lg:h-12" />
                                            <Skeleton className="h-8 w-2/3 sm:h-10 lg:h-12" />
                                        </div>
                                    )}
                                </h1>

                                {book ? (
                                    book.subtitle ? (
                                        <p className="mb-3 max-w-3xl text-base leading-relaxed text-muted-foreground italic sm:text-lg">
                                            {book.subtitle}
                                        </p>
                                    ) : null
                                ) : (
                                    <Skeleton className="mb-3 h-6 w-3/5" />
                                )}

                                <div className="mb-6 text-base font-medium text-muted-foreground sm:text-lg">
                                    {book ? (
                                        authorsData && authorsData.length > 0 ? (
                                            authorsData.map((author, index) => (
                                                <span key={author.slug}>
                                                    <Link
                                                        href={booksRoute.index.url({
                                                            query: { author: author.slug },
                                                        })}
                                                        className="text-primary hover:underline"
                                                    >
                                                        {author.name}
                                                    </Link>
                                                    {index < authorsData.length - 1 && ', '}
                                                </span>
                                            ))
                                        ) : book.authors.length > 0 ? (
                                            book.authors.join(', ')
                                        ) : (
                                            'Penulis anonim'
                                        )
                                    ) : (
                                        <Skeleton className="h-5 w-2/5" />
                                    )}
                                </div>

                                <div className="grid grid-cols-1 gap-3 sm:flex sm:flex-wrap sm:items-center">
                                    {book ? (
                                        <>
                                            <div
                                                className={`inline-flex w-full items-center gap-2 rounded-2xl border px-4 py-3 text-sm font-semibold sm:w-auto sm:rounded-full sm:py-2 ${availabilityBackground} ${availabilityColor}`}
                                            >
                                                <AvailabilityIcon className="size-4 shrink-0" />
                                                {availabilityLabel}
                                            </div>

                                            <div className="inline-flex w-full items-start gap-2 rounded-2xl border bg-muted/60 px-4 py-3 text-sm font-medium text-muted-foreground sm:w-auto sm:items-center sm:rounded-full sm:py-2">
                                                <Library className="mt-0.5 size-4 shrink-0 sm:mt-0" />
                                                <span className="min-w-0 text-left leading-relaxed sm:leading-normal">
                                                    {book.isBorrowable ? (
                                                        <>
                                                            <strong className="text-foreground">
                                                                {
                                                                    book.availableItemsCount
                                                                }
                                                            </strong>{' '}
                                                            dari {book.itemsCount}{' '}
                                                            eksemplar
                                                        </>
                                                    ) : (
                                                        <>
                                                            <strong className="text-foreground">
                                                                {book.itemsCount}
                                                            </strong>{' '}
                                                            eksemplar tersedia di
                                                            ruang baca
                                                        </>
                                                    )}
                                                </span>
                                            </div>

                                            {book.publishedYear ? (
                                                <div className="inline-flex w-full items-center gap-2 rounded-2xl border bg-muted/60 px-4 py-3 text-sm font-medium text-muted-foreground sm:w-auto sm:rounded-full sm:py-2">
                                                    <Calendar className="size-4 shrink-0" />
                                                    {book.publishedYear}
                                                </div>
                                            ) : null}

                                            <div className="inline-flex w-full items-start gap-2 rounded-2xl border bg-muted/60 px-4 py-3 text-sm font-medium text-muted-foreground sm:w-auto sm:items-center sm:rounded-full sm:py-2">
                                                <Eye className="mt-0.5 size-4 shrink-0 sm:mt-0" />
                                                <span className="min-w-0 text-left leading-relaxed sm:leading-normal">
                                                    <strong className="text-foreground">
                                                        {book.viewCount.toLocaleString(
                                                            'id-ID',
                                                        )}
                                                    </strong>{' '}
                                                    kali dilihat
                                                </span>
                                            </div>
                                        </>
                                    ) : (
                                        <>
                                            <Skeleton className="h-12 w-full rounded-2xl sm:h-10 sm:w-32 sm:rounded-full" />
                                            <Skeleton className="h-12 w-full rounded-2xl sm:h-10 sm:w-48 sm:rounded-full" />
                                            <Skeleton className="h-12 w-full rounded-2xl sm:h-10 sm:w-20 sm:rounded-full" />
                                            <Skeleton className="h-12 w-full rounded-2xl sm:h-10 sm:w-28 sm:rounded-full" />
                                        </>
                                    )}
                                </div>

                                <div className="mt-5 flex flex-wrap items-center gap-3">
                                    {book ? (
                                        <>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                className={cn(
                                                    'inline-flex h-auto items-center gap-2 rounded-full bg-background px-4 py-2 text-sm font-medium',
                                                    isBookmarkedByUser &&
                                                        'border-primary/40 bg-primary/10 text-primary hover:bg-primary/15',
                                                )}
                                                aria-label={
                                                    isBookmarkedByUser
                                                        ? 'Hapus bookmark'
                                                        : 'Simpan bookmark'
                                                }
                                                aria-pressed={isBookmarkedByUser}
                                                onClick={() =>
                                                    bookmarkRecord &&
                                                    toggleBookmark(bookmarkRecord)
                                                }
                                            >
                                                <Bookmark
                                                    className={
                                                        isBookmarkedByUser
                                                            ? 'fill-current'
                                                            : ''
                                                    }
                                                />
                                                {isBookmarkedByUser
                                                    ? 'Tersimpan'
                                                    : 'Simpan'}
                                            </Button>

                                            <KtiShareButton
                                                title={book.title}
                                                subtitle={
                                                    book.authors.join(', ') ||
                                                    'Penulis tidak tersedia'
                                                }
                                                kindLabel="Buku"
                                                className="bg-background"
                                            />

                                            {user &&
                                            auth.canBorrowBooks &&
                                            book.isBorrowable &&
                                            book.isAvailable ? (
                                                <Form
                                                    action={LoanRequestController.storeBook()}
                                                >
                                                    {({ processing }) => (
                                                        <>
                                                            <input
                                                                type="hidden"
                                                                name="book_id"
                                                                value={book.id}
                                                            />
                                                            <Button
                                                                type="submit"
                                                                variant="outline"
                                                                className="inline-flex h-auto items-center gap-2 rounded-full bg-background px-4 py-2 text-sm font-medium"
                                                                disabled={
                                                                    processing ||
                                                                    requestSummary.containsBook
                                                                }
                                                            >
                                                                <ShoppingCart className="size-4" />
                                                                {requestSummary.containsBook
                                                                    ? 'Di keranjang'
                                                                    : 'Pinjam'}
                                                            </Button>
                                                        </>
                                                    )}
                                                </Form>
                                            ) : null}
                                        </>
                                    ) : (
                                        <>
                                            <Skeleton className="h-10 w-24 rounded-full" />
                                            <Skeleton className="h-10 w-28 rounded-full" />
                                            <Skeleton className="h-10 w-24 rounded-full" />
                                        </>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            }
            sidebar={
                <div className="space-y-4">
                    <div className="rounded-2xl border bg-card shadow-sm">
                        <div className="p-5">
                            <h2 className="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                                Data Buku
                            </h2>
                        </div>
                        <Separator />
                        <div className="p-2">
                            {book ? (
                                <>
                                    {book.publisher ? (
                                        <KtiDetailItem
                                            icon={<Building2 className="size-4" />}
                                            label="Penerbit"
                                            value={
                                                publisherData ? (
                                                    <Link
                                                        href={booksRoute.index.url({
                                                            query: { publisher: publisherData.slug },
                                                        })}
                                                        className="text-primary hover:underline"
                                                    >
                                                        {publisherData.name}
                                                    </Link>
                                                ) : (
                                                    book.publisher
                                                )
                                            }
                                        />
                                    ) : null}
                                    {book.publishedYear ? (
                                        <KtiDetailItem
                                            icon={<Calendar className="size-4" />}
                                            label="Tahun"
                                            value={String(book.publishedYear)}
                                        />
                                    ) : null}
                                    {book.isbn || book.issn ? (
                                        <KtiDetailItem
                                            icon={<Hash className="size-4" />}
                                            label={book.isbn ? 'ISBN' : 'ISSN'}
                                            value={book.isbn ?? book.issn ?? '-'}
                                        />
                                    ) : null}
                                    {book.pages ? (
                                        <KtiDetailItem
                                            icon={<FileText className="size-4" />}
                                            label="Halaman"
                                            value={`${book.pages} halaman`}
                                        />
                                    ) : null}
                                    {book.displayShelfLocations.length > 0 ? (
                                        <div className="group flex items-start gap-3 rounded-xl p-3 transition-colors hover:bg-muted/50">
                                            <div className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                                <MapPinned className="size-4" />
                                            </div>
                                            <div className="min-w-0">
                                                <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                                    Lokasi rak
                                                </p>
                                                <p className="mt-0.5 text-sm font-semibold text-foreground">
                                                    {shelfLocations}
                                                </p>
                                                {book.usesBackupShelfLocations ? (
                                                    <p className="mt-1 text-xs leading-relaxed text-muted-foreground">
                                                        Rak utama sedang kosong,
                                                        jadi lokasi ini memakai rak
                                                        cadangan yang masih
                                                        tersedia.
                                                    </p>
                                                ) : null}
                                            </div>
                                        </div>
                                    ) : null}
                                    <KtiDetailItem
                                        icon={<Globe className="size-4" />}
                                        label="Bahasa"
                                        value={book.language ?? '-'}
                                    />
                                </>
                            ) : (
                                <>
                                    <KtiDetailItem
                                        icon={<Building2 className="size-4" />}
                                        label="Penerbit"
                                        value={<Skeleton className="h-5 w-28 animate-pulse" />}
                                    />
                                    <KtiDetailItem
                                        icon={<Calendar className="size-4" />}
                                        label="Tahun"
                                        value={<Skeleton className="h-5 w-16 animate-pulse" />}
                                    />
                                    <KtiDetailItem
                                        icon={<Hash className="size-4" />}
                                        label="ISBN"
                                        value={<Skeleton className="h-5 w-32 animate-pulse" />}
                                    />
                                    <KtiDetailItem
                                        icon={<FileText className="size-4" />}
                                        label="Halaman"
                                        value={<Skeleton className="h-5 w-20 animate-pulse" />}
                                    />
                                    <KtiDetailItem
                                        icon={<MapPinned className="size-4" />}
                                        label="Lokasi Rak"
                                        value={<Skeleton className="h-5 w-24 animate-pulse" />}
                                    />
                                    <KtiDetailItem
                                        icon={<Globe className="size-4" />}
                                        label="Bahasa"
                                        value={<Skeleton className="h-5 w-16 animate-pulse" />}
                                    />
                                </>
                            )}
                        </div>
                    </div>
                </div>
            }
            footer={
                (props.relatedBooks === undefined || props.relatedBooks.length > 0) && (
                    <KtiRelatedSection
                        title="Buku Terkait"
                        description="Rekomendasi buku lainnya dengan kategori atau topik serupa."
                    >
                        <Deferred
                            data="relatedBooks"
                            fallback={
                                <div className="grid gap-4 lg:grid-cols-2">
                                    <BookCardSkeleton variant="compact" />
                                    <BookCardSkeleton variant="compact" />
                                </div>
                            }
                            rescue={({ reloading }) => (
                                <DeferredCatalogRescue
                                    dataKey="relatedBooks"
                                    title="Daftar buku lain belum sempat dimuat"
                                    description="Coba muat lagi sebentar. Kalau berhasil, beberapa judul yang masih dekat dengan buku ini akan muncul di sini."
                                    reloading={reloading}
                                />
                            )}
                        >
                            <div className="grid gap-4 lg:grid-cols-2">
                                {props.relatedBooks?.map((relatedBook) => (
                                    <BookCard
                                        key={relatedBook.id}
                                        book={relatedBook}
                                        variant="compact"
                                        auth={auth}
                                        loanRequestCart={loanRequestCart}
                                    />
                                ))}
                            </div>
                        </Deferred>
                    </KtiRelatedSection>
                )
            }
        >
            <section>
                <div className="mb-5 flex items-center gap-3">
                    <h2 className="text-xl font-bold">Deskripsi</h2>
                </div>

                {book?.description ? (
                    <div className="space-y-4 text-base leading-[1.85] text-muted-foreground">
                        {book.description
                            .split('\n')
                            .filter(Boolean)
                            .map((paragraph, index) => (
                                <p key={index}>{paragraph}</p>
                            ))}
                    </div>
                ) : book ? (
                    <div className="rounded-2xl border border-dashed bg-muted/30 p-10 text-center">
                        <BookOpen className="mx-auto mb-3 size-10 text-muted-foreground/40" />
                        <p className="text-sm text-muted-foreground">
                            Sinopsis belum tersedia untuk buku ini.
                        </p>
                    </div>
                ) : (
                    <BookDescriptionSkeleton />
                )}
            </section>
        </KtiDetailPage>
    );
}

// test_compatibility: pt-24 pb-6 sm:pt-30 sm:pb-8 className="mb-6" className="flex min-h-[18rem] items-center justify-center sm:min-h-[22rem]"

