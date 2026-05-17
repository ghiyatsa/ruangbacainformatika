import { Link } from '@inertiajs/react';
import {
    ArrowUpRight,
    BookOpen,
    FileText,
    GraduationCap,
    Library,
} from 'lucide-react';
import booksRoute from '@/routes/books';
import internshipReportsRoute from '@/routes/internship-reports';
import skripsiRoute from '@/routes/skripsi';
import thesisRoute from '@/routes/thesis';
import SectionHeader from './SectionHeader';

export default function CatalogCtaGrid() {
    const catalogLinks = [
        {
            title: 'Katalog Buku',
            description: 'Buku dan referensi utama.',
            href: booksRoute.index.url(),
            icon: BookOpen,
        },
        {
            title: 'Skripsi',
            description: 'Arsip tugas akhir S1.',
            href: skripsiRoute.index.url(),
            icon: GraduationCap,
        },
        {
            title: 'Tesis',
            description: 'Karya ilmiah pascasarjana.',
            href: thesisRoute.index.url(),
            icon: Library,
        },
        {
            title: 'Laporan KP',
            description: 'Arsip kerja praktik.',
            href: internshipReportsRoute.index.url(),
            icon: FileText,
        },
    ];

    return (
        <div className="flex flex-col gap-6">
            <SectionHeader
                title="Jelajahi Katalog"
                subtitle="Akses cepat ke seluruh jenis koleksi akademik."
            />

            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                {catalogLinks.map((item) => {
                    const Icon = item.icon;

                    return (
                        <Link
                            key={item.title}
                            href={item.href}
                            className="group flex min-h-40 flex-col justify-between rounded-xl border bg-background p-5 transition-all duration-300 hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5"
                        >
                            <div className="flex items-start justify-between gap-4">
                                <div className="flex size-11 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <Icon className="size-5" />
                                </div>
                                <ArrowUpRight className="size-4 text-muted-foreground transition-colors group-hover:text-primary" />
                            </div>
                            <div className="mt-6 flex flex-col gap-1.5">
                                <h3 className="text-base font-bold transition-colors group-hover:text-primary">
                                    {item.title}
                                </h3>
                                <p className="text-sm leading-relaxed text-muted-foreground">
                                    {item.description}
                                </p>
                            </div>
                        </Link>
                    );
                })}
            </div>
        </div>
    );
}
