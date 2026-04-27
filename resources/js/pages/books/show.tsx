import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    BookOpen,
    Building2,
    Calendar,
    FileText,
    Hash,
} from 'lucide-react';
import CatalogController from '@/actions/App/Http/Controllers/CatalogController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import Footer from '@/components/welcome/Footer';
import Navigation from '@/components/welcome/Navigation';

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
            categories: string[];
            publisher: string | null;
            publishedYear: number | null;
            pages: number | null;
            language: string;
            itemsCount: number;
            availableItemsCount: number;
            isFeatured: boolean;
            isBorrowable: boolean;
            isAvailable: boolean;
        };
    };
    canRegister?: boolean;
}

export default function BookShow({
    book: { data: book },
    canRegister = true,
}: BookDetailsProps) {
    return (
        <div className="min-h-screen bg-background font-sans text-foreground selection:bg-primary/10 selection:text-primary">
            <Head title={`${book.title} — Ruang Baca`} />

            <div
                className="pointer-events-none fixed inset-0 z-0 opacity-[0.03] dark:opacity-[0.05]"
                style={{
                    backgroundImage:
                        'radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0)',
                    backgroundSize: '24px 24px',
                }}
            />

            <div className="relative z-10 flex min-h-screen flex-col">
                <Navigation canRegister={canRegister} />

                <main className="flex-1 py-10">
                    <div className="mx-auto max-w-5xl px-6 lg:px-8">
                        <div className="mb-8">
                            <Button
                                variant="ghost"
                                size="sm"
                                asChild
                                className="-ml-3 text-muted-foreground hover:text-foreground"
                            >
                                <Link href={CatalogController.url()}>
                                    <ArrowLeft className="mr-2 size-4" />
                                    Kembali ke Katalog
                                </Link>
                            </Button>
                        </div>

                        <div className="grid gap-10 md:grid-cols-12 md:gap-14">
                            {/* Left Column: Cover & Quick Stats */}
                            <div className="md:col-span-4 lg:col-span-3">
                                <div className="overflow-hidden rounded-xl border bg-muted shadow-lg">
                                    <img
                                        src={book.coverImageUrl}
                                        alt={`Cover buku ${book.title}`}
                                        className="aspect-3/4 w-full object-cover"
                                    />
                                </div>

                                <div className="mt-6 flex flex-col gap-3">
                                    <div className="flex items-center justify-between rounded-lg border bg-card p-3 shadow-sm">
                                        <div className="flex flex-col">
                                            <span className="text-xs font-medium text-muted-foreground">
                                                Ketersediaan
                                            </span>
                                            <span className="font-bold">
                                                {book.availableItemsCount} /{' '}
                                                {book.itemsCount}
                                            </span>
                                        </div>
                                        <Badge
                                            variant={
                                                !book.isAvailable
                                                    ? 'secondary'
                                                    : book.isBorrowable
                                                      ? 'default'
                                                      : 'secondary'
                                            }
                                        >
                                            {!book.isAvailable
                                                ? 'Kosong'
                                                : !book.isBorrowable
                                                  ? 'Referensi'
                                                  : 'Tersedia'}
                                        </Badge>
                                    </div>
                                </div>
                            </div>

                            {/* Right Column: Details */}
                            <div className="md:col-span-8 lg:col-span-9">
                                <div className="mb-4 flex flex-wrap gap-2">
                                    {book.categories.map((category) => (
                                        <Badge
                                            key={category}
                                            variant="secondary"
                                            className="font-medium"
                                        >
                                            {category}
                                        </Badge>
                                    ))}
                                    {book.isFeatured && (
                                        <Badge className="bg-primary/10 text-primary hover:bg-primary/20">
                                            ✦ Unggulan
                                        </Badge>
                                    )}
                                </div>

                                <h1 className="mb-2 text-3xl font-bold tracking-tight sm:text-4xl">
                                    {book.title}
                                </h1>

                                <p className="mb-8 text-lg font-medium text-muted-foreground">
                                    {book.authors.length > 0
                                        ? book.authors.join(', ')
                                        : 'Penulis anonim'}
                                </p>

                                <div className="mb-10 space-y-4">
                                    <h3 className="text-lg font-semibold">
                                        Sinopsis
                                    </h3>
                                    <div className="prose prose-sm dark:prose-invert max-w-none leading-relaxed text-muted-foreground">
                                        {book.description
                                            .split('\n')
                                            .map((paragraph, i) => (
                                                <p key={i}>{paragraph}</p>
                                            ))}
                                    </div>
                                </div>

                                <div className="rounded-xl border bg-card p-6 shadow-sm">
                                    <h3 className="mb-4 text-lg font-semibold">
                                        Informasi Detail
                                    </h3>

                                    <div className="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                                        <div className="flex items-start gap-3">
                                            <Building2 className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                                            <div>
                                                <p className="text-xs font-medium text-muted-foreground">
                                                    Penerbit
                                                </p>
                                                <p className="text-sm font-medium">
                                                    {book.publisher ||
                                                        'Tidak tersedia'}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="flex items-start gap-3">
                                            <Calendar className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                                            <div>
                                                <p className="text-xs font-medium text-muted-foreground">
                                                    Tahun Terbit
                                                </p>
                                                <p className="text-sm font-medium">
                                                    {book.publishedYear || '—'}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="flex items-start gap-3">
                                            <Hash className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                                            <div>
                                                <p className="text-xs font-medium text-muted-foreground">
                                                    ISBN / ISSN
                                                </p>
                                                <p className="text-sm font-medium">
                                                    {book.isbn ||
                                                        book.issn ||
                                                        '—'}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="flex items-start gap-3">
                                            <FileText className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                                            <div>
                                                <p className="text-xs font-medium text-muted-foreground">
                                                    Jumlah Halaman
                                                </p>
                                                <p className="text-sm font-medium">
                                                    {book.pages
                                                        ? `${book.pages} halaman`
                                                        : '—'}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="flex items-start gap-3">
                                            <BookOpen className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                                            <div>
                                                <p className="text-xs font-medium text-muted-foreground">
                                                    Bahasa
                                                </p>
                                                <p className="text-sm font-medium">
                                                    {book.language}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>

                <Footer />
            </div>
        </div>
    );
}
