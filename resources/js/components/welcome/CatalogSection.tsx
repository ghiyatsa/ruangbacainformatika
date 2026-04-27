import { Link } from '@inertiajs/react';
import {
    ArrowRight,
    BookOpen,
    GraduationCap,
    Sparkles,
} from 'lucide-react';
import CatalogController from '@/actions/App/Http/Controllers/CatalogController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import type { CatalogBook, WelcomeProps } from './types';

interface CatalogSectionProps {
    stats: WelcomeProps['stats'];
    featuredBook: WelcomeProps['featuredBook'];
    books: WelcomeProps['books'];
}

function availabilityLabel(book: CatalogBook): string {
    if (book.isAvailable) {
        return `${book.availableItemsCount} tersedia`;
    }
    return 'Tidak tersedia';
}

export default function CatalogSection({
    stats,
    featuredBook,
    books,
}: CatalogSectionProps) {
    /** Show at most 4 books in the preview */
    const previewBooks = books.data.slice(0, 4);

    return (
        <section className="py-20 lg:py-28">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="flex flex-col gap-16 lg:flex-row lg:items-start">

                    {/* Left — book grid preview */}
                    <div className="flex flex-1 flex-col gap-10">
                        {/* Section heading */}
                        <div className="flex flex-col gap-4">
                            <Badge className="w-fit" variant="secondary">
                                <GraduationCap className="mr-1.5 size-3.5" />
                                Koleksi Akademik Terkurasi
                            </Badge>
                            <h2 className="text-3xl font-bold tracking-tight sm:text-4xl">
                                Eksplorasi Katalog Digital Publik
                            </h2>
                            <p className="max-w-2xl text-muted-foreground">
                                Kami menyediakan akses terbuka untuk penelusuran pustaka guna
                                mendukung riset dan pembelajaran mahasiswa serta dosen Teknik
                                Informatika.
                            </p>
                        </div>

                        {/* Preview book grid */}
                        {previewBooks.length > 0 && (
                            <div className="grid gap-4 sm:grid-cols-2">
                                {previewBooks.map((book) => (
                                    <div
                                        key={book.id}
                                        className="group flex gap-4 overflow-hidden rounded-xl border border-muted-foreground/10 bg-card p-4 transition-all hover:border-primary/20 hover:shadow-md hover:-translate-y-0.5"
                                    >
                                        {/* Cover thumbnail */}
                                        <div className="h-28 w-20 shrink-0 overflow-hidden rounded-lg border bg-muted shadow-sm">
                                            <img
                                                src={book.coverImageUrl}
                                                alt={book.title}
                                                className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                            />
                                        </div>

                                        {/* Meta */}
                                        <div className="flex min-w-0 flex-1 flex-col justify-between py-0.5">
                                            <div className="space-y-1">
                                                {book.categories.slice(0, 1).map((c) => (
                                                    <Badge
                                                        key={c}
                                                        variant="outline"
                                                        className="h-5 px-1.5 text-[10px]"
                                                    >
                                                        {c}
                                                    </Badge>
                                                ))}
                                                <h3 className="line-clamp-2 text-sm font-bold leading-snug transition-colors group-hover:text-primary">
                                                    {book.title}
                                                </h3>
                                                <p className="line-clamp-1 text-xs text-muted-foreground">
                                                    {book.authors.join(', ') || 'Penulis anonim'}
                                                </p>
                                            </div>
                                            <div className="mt-2 flex items-center justify-between border-t pt-2 text-[10px]">
                                                <span className="font-medium text-muted-foreground uppercase">
                                                    {book.publishedYear || '—'}
                                                </span>
                                                <Badge
                                                    variant={book.isAvailable ? 'default' : 'secondary'}
                                                    className="h-5 px-1.5"
                                                >
                                                    {availabilityLabel(book)}
                                                </Badge>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}

                        {/* CTA — "More Books" */}
                        <div className="flex flex-col items-start gap-3 sm:flex-row sm:items-center">
                            <Button asChild size="lg" className="gap-2 rounded-xl">
                                <Link href={CatalogController.url()}>
                                    <BookOpen className="size-4" />
                                    Lihat Semua Buku
                                    <ArrowRight className="size-4" />
                                </Link>
                            </Button>
                            <p className="text-sm text-muted-foreground">
                                {stats.booksCount} judul tersedia ·{' '}
                                {stats.availableItemsCount} eksemplar siap dipinjam
                            </p>
                        </div>
                    </div>

                    {/* Right — spotlight / featured book */}
                    <aside className="sticky top-24 w-full lg:w-[360px] lg:shrink-0">
                        <Card className="overflow-hidden border-primary/20 bg-primary/5 shadow-inner">
                            <CardHeader className="pb-4">
                                <div className="flex items-center gap-2 text-primary">
                                    <Sparkles className="size-4" />
                                    <span className="text-xs font-bold tracking-widest uppercase">
                                        Koleksi Sorotan
                                    </span>
                                </div>
                                <CardTitle className="text-xl">Referensi Unggulan</CardTitle>
                            </CardHeader>

                            <CardContent className="space-y-4">
                                {featuredBook ? (
                                    <div className="space-y-4">
                                        <div className="aspect-3/4 overflow-hidden rounded-xl border bg-background shadow-lg">
                                            <img
                                                src={featuredBook.coverImageUrl}
                                                alt={featuredBook.title}
                                                className="h-full w-full object-cover"
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <h4 className="leading-tight font-bold">
                                                {featuredBook.title}
                                            </h4>
                                            <p className="line-clamp-3 text-sm leading-relaxed text-muted-foreground">
                                                {featuredBook.shortDescription}
                                            </p>
                                        </div>
                                        <div className="grid grid-cols-2 gap-2 text-[10px]">
                                            <div className="rounded-lg border bg-background p-2">
                                                <span className="mb-0.5 block text-muted-foreground uppercase">
                                                    Tahun
                                                </span>
                                                <span className="font-bold">
                                                    {featuredBook.publishedYear || '—'}
                                                </span>
                                            </div>
                                            <div className="rounded-lg border bg-background p-2">
                                                <span className="mb-0.5 block text-muted-foreground uppercase">
                                                    Ketersediaan
                                                </span>
                                                <span className="font-bold text-primary">
                                                    {availabilityLabel(featuredBook)}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="py-10 text-center text-muted-foreground">
                                        Belum ada koleksi sorotan.
                                    </div>
                                )}
                            </CardContent>

                            <CardFooter className="pt-0 pb-6">
                                <Button variant="secondary" className="w-full" asChild>
                                    <Link href={CatalogController.url()}>
                                        Jelajahi Semua Koleksi
                                        <ArrowRight className="ml-2 size-4" />
                                    </Link>
                                </Button>
                            </CardFooter>
                        </Card>
                    </aside>
                </div>
            </div>
        </section>
    );
}
