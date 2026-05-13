import { Link } from '@inertiajs/react';
import {
    BookOpen,
    Building2,
    Calendar,
    CheckCircle2,
    Eye,
    FileText,
    Globe,
    Hash,
    Library,
    Star,
    XCircle,
} from 'lucide-react';
import { CatalogReportCard } from '@/components/resource/CatalogReportCard';
import { ResourceDetailItem } from '@/components/resource/ResourceDetailItem';
import { ResourceDetailPage } from '@/components/resource/ResourceDetailPage';
import { Badge } from '@/components/ui/badge';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { Separator } from '@/components/ui/separator';
import type { BookData } from '@/features/books/types';
import booksRoute from '@/routes/books';

export interface BookDetailPageProps {
    book: { data: BookData };
    canRegister?: boolean;
}

export default function BookDetailPage({
    book: { data: book },
}: BookDetailPageProps) {
    const availabilityColor = !book.isBorrowable
        ? 'text-amber-600 dark:text-amber-400'
        : book.isAvailable
          ? 'text-emerald-600 dark:text-emerald-400'
          : 'text-red-600 dark:text-red-400';

    const availabilityLabel = !book.isBorrowable
        ? 'Hanya Referensi'
        : book.isAvailable
          ? 'Tersedia untuk Dipinjam'
          : 'Tidak Tersedia';

    const AvailabilityIcon =
        !book.isBorrowable || book.isAvailable ? CheckCircle2 : XCircle;

    const availabilityBackground = !book.isBorrowable
        ? 'bg-amber-500/10 border-amber-500/20'
        : book.isAvailable
          ? 'bg-emerald-500/10 border-emerald-500/20'
          : 'bg-red-500/10 border-red-500/20';

    return (
        <ResourceDetailPage
            title={book.title}
            description={
                book.description
                    ? book.description.slice(0, 160)
                    : `Detail buku ${book.title} di Ruang Baca Teknik Informatika Universitas Malikussaleh.`
            }
            hero={
                <div className="relative -mt-20 overflow-hidden sm:-mt-28">
                    <div
                        className="absolute inset-0 scale-110 blur-2xl"
                        style={{
                            backgroundImage: `url(${book.coverImageUrl})`,
                            backgroundSize: 'cover',
                            backgroundPosition: 'center',
                            opacity: 0.15,
                        }}
                    />
                    <div className="absolute inset-0 bg-linear-to-b from-background/30 via-background/60 to-background" />

                    <div className="relative mx-auto max-w-7xl px-6 pt-28 pb-12 sm:pt-36 lg:px-8">
                        <Breadcrumb className="mb-8">
                            <BreadcrumbList>
                                <BreadcrumbItem>
                                    <BreadcrumbLink asChild>
                                        <Link href="/">Beranda</Link>
                                    </BreadcrumbLink>
                                </BreadcrumbItem>
                                <BreadcrumbSeparator />
                                <BreadcrumbItem>
                                    <BreadcrumbLink asChild>
                                        <Link href={booksRoute.index.url()}>
                                            Katalog
                                        </Link>
                                    </BreadcrumbLink>
                                </BreadcrumbItem>
                                <BreadcrumbSeparator />
                                <BreadcrumbItem>
                                    <BreadcrumbPage className="max-w-xs truncate">
                                        {book.title}
                                    </BreadcrumbPage>
                                </BreadcrumbItem>
                            </BreadcrumbList>
                        </Breadcrumb>

                        <div className="grid items-center gap-10 md:grid-cols-12 md:gap-12">
                            <div className="md:col-span-3">
                                <div className="group relative overflow-hidden rounded-2xl border border-white/10">
                                    <img
                                        src={book.coverImageUrl}
                                        alt={`Cover buku ${book.title}`}
                                        className="aspect-3/4 w-full object-cover"
                                    />
                                    <div className="absolute inset-0 bg-linear-to-br from-white/5 to-transparent" />
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

                                    {book.categories.map((category) => (
                                        <Link
                                            key={category.slug}
                                            href={booksRoute.index.url({
                                                query: {
                                                    category: category.slug,
                                                },
                                            })}
                                        >
                                            <Badge
                                                variant="secondary"
                                                className="bg-muted/80 backdrop-blur-sm"
                                            >
                                                {category.name}
                                            </Badge>
                                        </Link>
                                    ))}
                                </div>

                                <h1 className="mb-3 text-3xl leading-tight font-bold tracking-tight sm:text-4xl lg:text-5xl">
                                    {book.title}
                                </h1>

                                <p className="mb-6 text-base font-medium text-muted-foreground sm:text-lg">
                                    {book.authors.length > 0
                                        ? book.authors.join(', ')
                                        : 'Penulis anonim'}
                                </p>

                                <div className="flex flex-wrap items-center gap-3">
                                    <div
                                        className={`inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold ${availabilityBackground} ${availabilityColor}`}
                                    >
                                        <AvailabilityIcon className="size-4" />
                                        {availabilityLabel}
                                    </div>

                                    <div className="inline-flex items-center gap-2 rounded-full border bg-muted/60 px-4 py-2 text-sm font-medium text-muted-foreground backdrop-blur-sm">
                                        <Library className="size-4" />
                                        <span>
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
                                                    perpustakaan
                                                </>
                                            )}
                                        </span>
                                    </div>

                                    {book.publishedYear ? (
                                        <div className="inline-flex items-center gap-2 rounded-full border bg-muted/60 px-4 py-2 text-sm font-medium text-muted-foreground backdrop-blur-sm">
                                            <Calendar className="size-4" />
                                            {book.publishedYear}
                                        </div>
                                    ) : null}

                                    <div className="inline-flex items-center gap-2 rounded-full border bg-muted/60 px-4 py-2 text-sm font-medium text-muted-foreground backdrop-blur-sm">
                                        <Eye className="size-4" />
                                        <span>
                                            <strong className="text-foreground">
                                                {book.viewCount.toLocaleString(
                                                    'id-ID',
                                                )}
                                            </strong>{' '}
                                            kali dilihat
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            }
            sidebar={
                <div className="space-y-4">
                    <div className="rounded-2xl border bg-card/80 shadow-sm backdrop-blur-sm">
                        <div className="p-5">
                            <h2 className="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                                Informasi Buku
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
                                    label="Tahun Terbit"
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
                                    label="Jumlah Halaman"
                                    value={`${book.pages} halaman`}
                                />
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
