import { Head, Link } from '@inertiajs/react';
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
import type { ReactNode } from 'react';
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
import books from '@/routes/books';

export interface BookDetailPageProps {
    book: { data: BookData };
    canRegister?: boolean;
}

// ─── Detail Item ──────────────────────────────────────────────────────────────

function DetailItem({
    icon,
    label,
    value,
}: {
    icon: ReactNode;
    label: string;
    value: string;
}) {
    return (
        <div className="group flex items-start gap-3 rounded-xl p-3 transition-colors hover:bg-muted/50">
            <div className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                {icon}
            </div>
            <div className="min-w-0">
                <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                    {label}
                </p>
                <p className="mt-0.5 truncate text-sm font-semibold text-foreground">
                    {value}
                </p>
            </div>
        </div>
    );
}

// ─── Page ─────────────────────────────────────────────────────────────────────

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

    const availabilityBg = !book.isBorrowable
        ? 'bg-amber-500/10 border-amber-500/20'
        : book.isAvailable
          ? 'bg-emerald-500/10 border-emerald-500/20'
          : 'bg-red-500/10 border-red-500/20';

    return (
        <>
            <Head title={book.title} />

            <div
                className="pointer-events-none fixed inset-0 z-0 opacity-[0.03] dark:opacity-[0.05]"
                style={{
                    backgroundImage:
                        'radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0)',
                    backgroundSize: '24px 24px',
                }}
            />

            <div className="relative z-10">
                {/* Hero banner with blurred cover */}
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
                                        <Link href={books.index.url()}>
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
                            {/* Cover */}
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

                            {/* Title & Meta */}
                            <div className="flex flex-col justify-center md:col-span-9">
                                <div className="mb-3 flex flex-wrap gap-1.5">
                                    {book.isFeatured && (
                                        <Badge className="gap-1 border-primary/20 bg-primary/15 text-primary hover:bg-primary/20">
                                            <Star className="size-3 fill-current" />
                                            Unggulan
                                        </Badge>
                                    )}
                                    {book.categories.map((category) => (
                                        <Link
                                            key={category.slug}
                                            href={books.index.url({
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
                                        className={`inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold ${availabilityBg} ${availabilityColor}`}
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

                                    {book.publishedYear && (
                                        <div className="inline-flex items-center gap-2 rounded-full border bg-muted/60 px-4 py-2 text-sm font-medium text-muted-foreground backdrop-blur-sm">
                                            <Calendar className="size-4" />
                                            {book.publishedYear}
                                        </div>
                                    )}

                                    <div className="inline-flex items-center gap-2 rounded-full border bg-muted/60 px-4 py-2 text-sm font-medium text-muted-foreground backdrop-blur-sm">
                                        <Eye className="size-4" />
                                        <span>
                                            <strong className="text-foreground">
                                                {book.viewCount.toLocaleString()}
                                            </strong>{' '}
                                            kali dilihat
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Main Content */}
                <div className="mx-auto max-w-7xl px-6 lg:px-8">
                    <div className="grid gap-8 md:grid-cols-12 md:gap-10">
                        {/* Sidebar */}
                        <aside className="md:col-span-4 lg:col-span-3">
                            <div className="rounded-2xl border bg-card/80 shadow-sm backdrop-blur-sm">
                                <div className="p-5">
                                    <h2 className="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                                        Informasi Buku
                                    </h2>
                                </div>
                                <Separator />
                                <div className="p-2">
                                    {book.publisher && (
                                        <DetailItem
                                            icon={
                                                <Building2 className="size-4" />
                                            }
                                            label="Penerbit"
                                            value={book.publisher}
                                        />
                                    )}
                                    {book.publishedYear && (
                                        <DetailItem
                                            icon={
                                                <Calendar className="size-4" />
                                            }
                                            label="Tahun Terbit"
                                            value={String(book.publishedYear)}
                                        />
                                    )}
                                    {(book.isbn || book.issn) && (
                                        <DetailItem
                                            icon={<Hash className="size-4" />}
                                            label={book.isbn ? 'ISBN' : 'ISSN'}
                                            value={
                                                book.isbn || book.issn || '—'
                                            }
                                        />
                                    )}
                                    {book.pages && (
                                        <DetailItem
                                            icon={
                                                <FileText className="size-4" />
                                            }
                                            label="Jumlah Halaman"
                                            value={`${book.pages} halaman`}
                                        />
                                    )}
                                    <DetailItem
                                        icon={<Globe className="size-4" />}
                                        label="Bahasa"
                                        value={book.language}
                                    />
                                </div>
                            </div>
                        </aside>

                        {/* Synopsis */}
                        <div className="md:col-span-8 lg:col-span-9">
                            <section>
                                <div className="mb-5 flex items-center gap-3">
                                    <div className="flex size-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                        <BookOpen className="size-4" />
                                    </div>
                                    <h2 className="text-xl font-bold">
                                        Sinopsis
                                    </h2>
                                </div>

                                {book.description ? (
                                    <div className="space-y-4 text-base leading-[1.85] text-muted-foreground">
                                        {book.description
                                            .split('\n')
                                            .filter(Boolean)
                                            .map((paragraph, i) => (
                                                <p key={i}>{paragraph}</p>
                                            ))}
                                    </div>
                                ) : (
                                    <div className="rounded-2xl border border-dashed bg-muted/30 p-10 text-center">
                                        <BookOpen className="mx-auto mb-3 size-10 text-muted-foreground/40" />
                                        <p className="text-sm text-muted-foreground">
                                            Sinopsis belum tersedia untuk buku
                                            ini.
                                        </p>
                                    </div>
                                )}
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

