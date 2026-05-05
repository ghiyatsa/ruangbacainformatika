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
import React from 'react';
import { AppHeader } from '@/components/layouts/AppHeader';
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
import Footer from '@/components/welcome/Footer';
import books from '@/routes/books';

interface BookDetailsProps {
    book: {
        data: {
            id: number;
            title: string;
            slug: string;
            isbn: string | null;
            issn: string | null;
            description: string;
            coverImageUrl: string;
            authors: string[];
            categories: { name: string; slug: string }[];
            publisher: string | null;
            publishedYear: number | null;
            pages: number | null;
            language: string;
            itemsCount: number;
            availableItemsCount: number;
            isFeatured: boolean;
            isBorrowable: boolean;
            isAvailable: boolean;
            viewCount: number;
        };
    };
    canRegister?: boolean;
}

interface DetailItemProps {
    icon: React.ReactNode;
    label: string;
    value: string;
}

function DetailItem({ icon, label, value }: DetailItemProps) {
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

export default function BookShow({ book: { data: book } }: BookDetailsProps) {
    // Reference books (isBorrowable=false) are always available in the library
    // since they cannot be borrowed — they stay on the shelf permanently.
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
        <div className="min-h-screen bg-background font-sans text-foreground selection:bg-primary/10 selection:text-primary">
            <Head title={`${book.title} — Ruang Baca`} />

            {/* Subtle dot-grid background */}
            <div
                className="pointer-events-none fixed inset-0 z-0 opacity-[0.03] dark:opacity-[0.05]"
                style={{
                    backgroundImage:
                        'radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0)',
                    backgroundSize: '24px 24px',
                }}
            />

            <div className="relative z-10 flex min-h-screen flex-col">
                <AppHeader />

                {/* Hero Banner with blurred cover */}
                <div className="relative overflow-hidden">
                    {/* Blurred background cover */}
                    <div
                        className="absolute inset-0 scale-110 blur-2xl"
                        style={{
                            backgroundImage: `url(${book.coverImageUrl})`,
                            backgroundSize: 'cover',
                            backgroundPosition: 'center',
                            opacity: 0.15,
                        }}
                    />
                    {/* Gradient overlay */}
                    <div className="absolute inset-0 bg-gradient-to-b from-background/30 via-background/60 to-background" />

                    <div className="relative mx-auto max-w-6xl px-6 pt-8 pb-0 lg:px-8">
                        {/* Breadcrumb */}
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

                        {/* Main hero content */}
                        <div className="grid gap-10 md:grid-cols-12 md:gap-12">
                            {/* Cover */}
                            <div className="md:col-span-3 lg:col-span-3">
                                <div className="group relative overflow-hidden rounded-2xl border border-white/10 shadow-2xl shadow-black/20 dark:shadow-black/40">
                                    <img
                                        src={book.coverImageUrl}
                                        alt={`Cover buku ${book.title}`}
                                        className="aspect-3/4 w-full object-cover"
                                    />
                                    {/* Sheen effect */}
                                    <div className="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent" />
                                </div>
                            </div>

                            {/* Title & Meta */}
                            <div className="flex flex-col justify-end pb-10 md:col-span-9 lg:col-span-9">
                                {/* Category badges */}
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
                                            href={books.categories.show.url(
                                                category.slug,
                                            )}
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

                                {/* Title */}
                                <h1 className="mb-3 text-3xl leading-tight font-bold tracking-tight sm:text-4xl lg:text-5xl">
                                    {book.title}
                                </h1>

                                {/* Authors */}
                                <p className="mb-6 text-base font-medium text-muted-foreground sm:text-lg">
                                    {book.authors.length > 0
                                        ? book.authors.join(', ')
                                        : 'Penulis anonim'}
                                </p>

                                {/* Quick stats row */}
                                <div className="flex flex-wrap items-center gap-3">
                                    {/* Availability pill */}
                                    <div
                                        className={`inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold ${availabilityBg} ${availabilityColor}`}
                                    >
                                        <AvailabilityIcon className="size-4" />
                                        {availabilityLabel}
                                    </div>

                                    {/* Stock pill */}
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
                <main className="flex-1 py-10">
                    <div className="mx-auto max-w-6xl px-6 lg:px-8">
                        <div className="grid gap-8 md:grid-cols-12 md:gap-10">
                            {/* Left sticky sidebar */}
                            <aside className="md:col-span-4 lg:col-span-3">
                                {/* Detail info card */}
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
                                                value={String(
                                                    book.publishedYear,
                                                )}
                                            />
                                        )}
                                        {(book.isbn || book.issn) && (
                                            <DetailItem
                                                icon={
                                                    <Hash className="size-4" />
                                                }
                                                label={
                                                    book.isbn ? 'ISBN' : 'ISSN'
                                                }
                                                value={
                                                    book.isbn ||
                                                    book.issn ||
                                                    '—'
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

                            {/* Right main content */}
                            <div className="md:col-span-8 lg:col-span-9">
                                {/* Synopsis */}
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
                                                Sinopsis belum tersedia untuk
                                                buku ini.
                                            </p>
                                        </div>
                                    )}
                                </section>
                            </div>
                        </div>
                    </div>
                </main>

                <Footer />
            </div>
        </div>
    );
}
