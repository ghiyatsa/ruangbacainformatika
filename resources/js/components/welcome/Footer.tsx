import { Link } from '@inertiajs/react';
import { BookOpen, Globe, Terminal } from 'lucide-react';
import { Button } from '@/components/ui/button';
import books from '@/routes/books';

export default function Footer() {
    return (
        <footer className="border-t bg-muted/20">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                {/* Main footer content */}
                <div className="py-10 sm:py-12">
                    <div className="flex flex-col gap-8 sm:flex-row sm:items-start sm:justify-between">
                        {/* Brand */}
                        <div className="flex flex-col gap-3">
                            <div className="flex items-center gap-2">
                                <Terminal className="size-5 text-primary" />
                                <span className="text-sm font-bold tracking-wider uppercase">
                                    Ruang Baca
                                </span>
                            </div>
                            <p className="max-w-xs text-xs leading-relaxed text-muted-foreground">
                                Perpustakaan digital Prodi Teknik Informatika
                                Universitas Malikussaleh — mendukung riset dan
                                pembelajaran akademik.
                            </p>
                        </div>

                        {/* Quick links */}
                        <div className="flex flex-col gap-3">
                            <h4 className="text-xs font-semibold tracking-wider uppercase text-foreground">
                                Katalog
                            </h4>
                            <div className="flex flex-col gap-2">
                                <Link
                                    href={books.index.url()}
                                    className="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    <BookOpen className="size-3.5" />
                                    Semua Buku
                                </Link>
                                <Link
                                    href={`${books.index.url()}?filter=available`}
                                    className="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    <BookOpen className="size-3.5" />
                                    Tersedia Dipinjam
                                </Link>
                                <Link
                                    href={`${books.index.url()}?filter=featured`}
                                    className="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    <BookOpen className="size-3.5" />
                                    Koleksi Unggulan
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Bottom bar */}
                <div className="flex flex-col items-center justify-between gap-4 border-t py-6 sm:flex-row">
                    <p className="text-center text-xs text-muted-foreground sm:text-left">
                        &copy; {new Date().getFullYear()} Prodi Teknik
                        Informatika Universitas Malikussaleh. Built for
                        Informatics Excellence.
                    </p>
                    <Button
                        variant="ghost"
                        size="icon"
                        className="h-8 w-8 rounded-full"
                        asChild
                    >
                        <a
                            href="#"
                            aria-label="Website Universitas Malikussaleh"
                        >
                            <Globe className="size-4" />
                        </a>
                    </Button>
                </div>
            </div>
        </footer>
    );
}
