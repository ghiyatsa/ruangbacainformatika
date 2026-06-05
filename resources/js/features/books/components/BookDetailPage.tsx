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
import LoanRequestController from '@/actions/App/Http/Controllers/LoanRequestController';
import { Breadcrumbs } from '@/components/common/Breadcrumbs';
import { CatalogReportCard } from '@/components/resource/CatalogReportCard';
import { CatalogShareButton } from '@/components/resource/CatalogShareButton';
import { RelatedCatalogSection } from '@/components/resource/RelatedCatalogSection';
import { RelatedCatalogSectionSkeleton } from '@/components/resource/RelatedCatalogSectionSkeleton';
import { ResourceDetailItem } from '@/components/resource/ResourceDetailItem';
import { ResourceDetailPage } from '@/components/resource/ResourceDetailPage';
import { ResourceDetailPageSkeleton } from '@/components/resource/ResourceDetailPageSkeleton';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import BookCard from '@/features/books/components/BookCard';
import DeferredCatalogRescue from '@/features/welcome/components/catalog/DeferredCatalogRescue';
import { useCatalogBookmarks } from '@/hooks/use-catalog-bookmarks';
import { cn } from '@/lib/utils';
import booksRoute from '@/routes/books';
import type { BookData, LoanRequestSummary } from '@/features/books/types';
import type { CatalogBookmarkRecord } from '@/hooks/use-catalog-bookmarks';
import type { Auth, LoanRequestCart } from '@/types';

export interface BookDetailPageProps {
    book?: { data: BookData };
    loanRequest?: LoanRequestSummary | null;
    relatedBooks?: BookData[];
    loading?: boolean;
}

export default function BookDetailPage(props: BookDetailPageProps) {
    const { auth, loanRequestCart } = usePage<{
        auth: Auth;
        loanRequestCart: LoanRequestCart | null;
    }>().props;
    const user = auth.user;
    const { isBookmarked, toggleBookmark } = useCatalogBookmarks();

    if (props.loading || !props.book?.data) {
        return (
            <ResourceDetailPageSkeleton
                variant="book"
                contentTitle="Sinopsis"
            />
        );
    }

    const {
        book: { data: book },
        loanRequest,
    } = props;
    const requestSummary = loanRequest ?? {
        count: 0,
        maxBooks: 0,
        activeLoansCount: 0,
        containsBook: false,
        hasActiveQr: false,
    };
    const isBookmarkedByUser = isBookmarked({
        catalogType: 'book',
        id: book.id,
    });
    const bookmarkRecord: CatalogBookmarkRecord = {
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
    };
    const availabilityColor = !book.isBorrowable
        ? 'text-amber-600 dark:text-amber-400'
        : book.isAvailable
          ? 'text-emerald-600 dark:text-emerald-400'
          : 'text-red-600 dark:text-red-400';

    const availabilityLabel = !book.isBorrowable
        ? 'Baca di tempat'
        : book.isAvailable
          ? 'Tersedia'
          : 'Tidak Tersedia';

    const AvailabilityIcon =
        !book.isBorrowable || book.isAvailable ? CheckCircle2 : XCircle;

    const availabilityBackground = !book.isBorrowable
        ? 'bg-amber-500/10 border-amber-500/20'
        : book.isAvailable
          ? 'bg-emerald-500/10 border-emerald-500/20'
          : 'bg-red-500/10 border-red-500/20';
    const seoKeywords = [
        book.title,
        ...book.authors,
        ...book.categories.map((category) => category.name),
        'katalog buku',
        'ruang baca informatika',
    ].filter((value): value is string => Boolean(value));
    const shelfLocations = book.displayShelfLocations.join(', ');

    return (
        <ResourceDetailPage
            title={book.title}
            description={
                book.description
                    ? book.description.slice(0, 160)
                    : `${book.title} tersedia di Ruang Baca Teknik Informatika Universitas Malikussaleh.`
            }
            image={book.coverImageUrl}
            keywords={seoKeywords}
            showBackground={false}
            deferSecondaryContent
            contentClassName="pt-2 pb-10 sm:pt-3"
            hero={
                <div className="relative -mt-20 overflow-hidden sm:-mt-28">
                    <div
                        className="pointer-events-none absolute inset-0"
                        aria-hidden="true"
                    >
                        <div className="absolute top-[8%] left-[10%] h-40 w-40 rounded-full bg-primary/12 blur-3xl" />
                        <div className="absolute right-[8%] bottom-[12%] h-56 w-56 rounded-full bg-primary/10 blur-3xl" />
                    </div>
                    <div className="absolute inset-0 bg-linear-to-b from-background/30 via-background/60 to-background" />

                    <div className="relative mx-auto max-w-7xl px-6 pt-24 pb-6 sm:pt-30 sm:pb-8 lg:px-8">
                        <div className="mb-6">
                            <Breadcrumbs
                                breadcrumbs={[
                                    { title: 'Beranda', href: '/' },
                                    {
                                        title: 'Katalog Buku',
                                        href: booksRoute.index.url(),
                                    },
                                    {
                                        title: book.title,
                                        href: booksRoute.show.url(book.slug),
                                    },
                                ]}
                            />
                        </div>

                        <div className="grid items-center gap-8 md:grid-cols-12 md:gap-8">
                            <div className="md:col-span-3">
                                <div className="flex items-center justify-center">
                                    <div className="aspect-3/4 w-[65vw] max-w-72 overflow-hidden rounded-2xl bg-muted shadow-2xl ring-1 shadow-black/10 ring-border/70 sm:w-72 md:w-full">
                                        <img
                                            src={book.coverImageUrl}
                                            alt={`Cover buku ${book.title}`}
                                            fetchPriority="high"
                                            decoding="async"
                                            sizes="(min-width: 1024px) 20rem, (min-width: 768px) 28vw, 65vw"
                                            className="h-full w-full object-cover"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div className="flex flex-col justify-center md:col-span-9">
                                <div className="mb-3 flex flex-wrap gap-1.5">
                                    {book.isFeatured ? (
                                        <Badge className="gap-1 border-primary/20 bg-primary/15 text-primary hover:bg-primary/20">
                                            <Star className="size-3 fill-current" />
                                            Unggulan
                                        </Badge>
                                    ) : null}

                                    {book.categories.map((category, index) => (
                                        <Link
                                            key={
                                                category.slug ??
                                                `${category.name}-${index}`
                                            }
                                            href={booksRoute.index.url({
                                                query: {
                                                    category: category.slug,
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
                                    ))}
                                </div>

                                <h1 className="mb-3 text-3xl leading-tight font-bold tracking-tight sm:text-4xl lg:text-5xl">
                                    {book.title}
                                </h1>

                                {book.subtitle ? (
                                    <p className="mb-3 max-w-3xl text-base leading-relaxed text-muted-foreground italic sm:text-lg">
                                        {book.subtitle}
                                    </p>
                                ) : null}

                                <p className="mb-6 text-base font-medium text-muted-foreground sm:text-lg">
                                    {book.authors.length > 0
                                        ? book.authors.join(', ')
                                        : 'Penulis anonim'}
                                </p>

                                <div className="grid grid-cols-1 gap-3 sm:flex sm:flex-wrap sm:items-center">
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
                                                    eksemplar tersedia di ruang
                                                    baca
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
                                </div>

                                <div className="mt-5 flex flex-wrap items-center gap-3">
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

                                    <CatalogShareButton
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
                            {book.publisher ? (
                                <ResourceDetailItem
                                    icon={<Building2 className="size-4" />}
                                    label="Penerbit"
                                    value={book.publisher}
                                />
                            ) : null}
                            {book.publishedYear ? (
                                <ResourceDetailItem
                                    icon={<Calendar className="size-4" />}
                                    label="Tahun"
                                    value={String(book.publishedYear)}
                                />
                            ) : null}
                            {book.isbn || book.issn ? (
                                <ResourceDetailItem
                                    icon={<Hash className="size-4" />}
                                    label={book.isbn ? 'ISBN' : 'ISSN'}
                                    value={book.isbn ?? book.issn ?? '-'}
                                />
                            ) : null}
                            {book.pages ? (
                                <ResourceDetailItem
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
                                                Rak utama sedang kosong, jadi
                                                lokasi ini memakai rak cadangan
                                                yang masih tersedia.
                                            </p>
                                        ) : null}
                                    </div>
                                </div>
                            ) : null}
                            <ResourceDetailItem
                                icon={<Globe className="size-4" />}
                                label="Bahasa"
                                value={book.language ?? '-'}
                            />
                        </div>
                    </div>

                    <CatalogReportCard
                        catalogType="book"
                        catalogId={book.id}
                        catalogLabel="Buku"
                        catalogTitle={book.title}
                    />
                </div>
            }
            footer={
                <Deferred
                    data="relatedBooks"
                    fallback={<RelatedCatalogSectionSkeleton variant="book" />}
                    rescue={({ reloading }) => (
                        <DeferredCatalogRescue
                            dataKey="relatedBooks"
                            title="Daftar buku lain belum sempat dimuat"
                            description="Coba muat lagi sebentar. Kalau berhasil, beberapa judul yang masih dekat dengan buku ini akan muncul di sini."
                            reloading={reloading}
                        />
                    )}
                >
                    {props.relatedBooks && props.relatedBooks.length > 0 ? (
                        <RelatedCatalogSection
                            title="Buku lain yang mungkin cocok"
                            description="Kalau topik atau kategorinya terasa pas, beberapa judul ini bisa jadi bacaan berikutnya."
                        >
                            <div className="grid gap-4 lg:grid-cols-2">
                                {props.relatedBooks.map((relatedBook) => (
                                    <BookCard
                                        key={relatedBook.id}
                                        book={relatedBook}
                                        variant="compact"
                                        auth={auth}
                                        loanRequestCart={loanRequestCart}
                                    />
                                ))}
                            </div>
                        </RelatedCatalogSection>
                    ) : null}
                </Deferred>
            }
        >
            <section>
                <div className="mb-5 flex items-center gap-3">
                    <div className="flex size-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <BookOpen className="size-4" />
                    </div>
                    <h2 className="text-xl font-bold">Sinopsis</h2>
                </div>

                {book.description ? (
                    <div className="space-y-4 text-base leading-[1.85] text-muted-foreground">
                        {book.description
                            .split('\n')
                            .filter(Boolean)
                            .map((paragraph, index) => (
                                <p key={index}>{paragraph}</p>
                            ))}
                    </div>
                ) : (
                    <div className="rounded-2xl border border-dashed bg-muted/30 p-10 text-center">
                        <BookOpen className="mx-auto mb-3 size-10 text-muted-foreground/40" />
                        <p className="text-sm text-muted-foreground">
                            Sinopsis belum tersedia untuk buku ini.
                        </p>
                    </div>
                )}
            </section>
        </ResourceDetailPage>
    );
}
