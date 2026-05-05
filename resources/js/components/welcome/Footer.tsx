import { Link } from '@inertiajs/react';
import {
    BookOpen,
    BookText,
    ExternalLink,
    Github,
    Globe,
    GraduationCap,
    Heart,
    LogIn,
    Mail,
    MapPin,
    Search,
    Terminal,
    UserPlus,
    Wrench,
} from 'lucide-react';
import type { Variants } from 'motion/react';
import { motion } from 'motion/react';
import { home, login, register, search } from '@/routes';
import books from '@/routes/books';
import similarity from '@/routes/similarity';

const CATALOG_LINKS = [
    { label: 'Semua Buku', href: () => books.index.url(), icon: BookOpen },
    {
        label: 'Tersedia Dipinjam',
        href: () => `${books.index.url()}?filter=available`,
        icon: BookText,
    },
    {
        label: 'Koleksi Unggulan',
        href: () => `${books.index.url()}?filter=featured`,
        icon: BookText,
    },
];

const INFO_LINKS = [
    {
        label: 'Universitas Malikussaleh',
        href: 'https://www.unimal.ac.id',
        external: true,
    },
    {
        label: 'Prodi Teknik Informatika',
        href: 'https://informatika.unimal.ac.id',
        external: true,
    },
    {
        label: 'Sistem Informasi Akademik',
        href: 'https://sia.unimal.ac.id',
        external: true,
    },
];

const SERVICE_LINKS = [
    { label: 'Beranda', href: () => home.url(), icon: Terminal, internal: true },
    { label: 'Cari Buku', href: () => search.url(), icon: Search, internal: true },
    {
        label: 'Pinjam Buku',
        href: () => `${books.index.url()}?filter=available`,
        icon: BookOpen,
        internal: true,
    },
    {
        label: 'Cek Kemiripan',
        href: () => similarity.index.url(),
        icon: Wrench,
        internal: true,
    },
    { label: 'Masuk', href: () => login.url(), icon: LogIn, internal: true },
    { label: 'Daftar', href: () => register.url(), icon: UserPlus, internal: true },
];

const fadeUp: Variants = {
    hidden: { opacity: 0, y: 18 },
    show: (i: number) => ({
        opacity: 1,
        y: 0,
        transition: { duration: 0.45, ease: 'easeOut' as const, delay: i * 0.08 },
    }),
};

export default function Footer() {
    const year = new Date().getFullYear();

    return (
        <footer className="relative overflow-hidden border-t">
            {/* Subtle background glow */}
            <div
                className="pointer-events-none absolute inset-0 -z-10"
                aria-hidden="true"
            >
                <div className="absolute -bottom-32 left-1/2 h-[400px] w-[700px] -translate-x-1/2 rounded-full bg-primary/5 blur-[100px] dark:bg-primary/8" />
            </div>

            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                {/* ── Main grid ────────────────────────────────────────── */}
                <div className="grid gap-12 py-14 sm:py-16 md:grid-cols-2 lg:grid-cols-12 xl:grid-cols-12">
                    {/* Brand column */}
                    <motion.div
                        className="lg:col-span-4"
                        custom={0}
                        variants={fadeUp}
                        initial="hidden"
                        whileInView="show"
                        viewport={{ once: true, amount: 0.3 }}
                    >
                        {/* Logo */}
                        <Link
                            href={home.url()}
                            className="group mb-5 inline-flex items-center gap-3"
                        >
                            <div className="flex size-10 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-md shadow-primary/25 transition-shadow duration-200 group-hover:shadow-lg group-hover:shadow-primary/35">
                                <Terminal className="size-5" />
                            </div>
                            <div className="flex flex-col">
                                <span className="text-sm font-bold tracking-wider uppercase">
                                    Ruang Baca
                                </span>
                                <span className="text-[10px] font-medium text-muted-foreground">
                                    Teknik Informatika UNIMAL
                                </span>
                            </div>
                        </Link>

                        <p className="mb-6 max-w-sm text-sm leading-relaxed text-muted-foreground">
                            Perpustakaan digital resmi Program Studi Teknik
                            Informatika Universitas Malikussaleh. Mendukung
                            riset, pembelajaran akademik, dan pengembangan
                            literasi teknologi mahasiswa.
                        </p>

                        {/* Contact details */}
                        <div className="flex flex-col gap-2.5">
                            <div className="flex items-start gap-2.5 text-xs text-muted-foreground">
                                <MapPin className="mt-0.5 size-3.5 shrink-0 text-primary/70" />
                                <span>
                                    Jl. Cot Tengku Nie, Reuleut, Aceh Utara —
                                    24355
                                </span>
                            </div>
                            <div className="flex items-center gap-2.5 text-xs text-muted-foreground">
                                <Mail className="size-3.5 shrink-0 text-primary/70" />
                                <a
                                    href="mailto:informatika@unimal.ac.id"
                                    className="transition-colors hover:text-foreground"
                                >
                                    informatika@unimal.ac.id
                                </a>
                            </div>
                        </div>
                    </motion.div>

                    {/* Spacer on lg */}
                    <div className="hidden lg:col-span-1 lg:block" />

                    {/* Layanan (Services) links */}
                    <motion.div
                        className="lg:col-span-2"
                        custom={1}
                        variants={fadeUp}
                        initial="hidden"
                        whileInView="show"
                        viewport={{ once: true, amount: 0.3 }}
                    >
                        <h4 className="mb-4 flex items-center gap-2 text-xs font-semibold tracking-widest text-foreground/80 uppercase">
                            <Wrench className="size-3.5 text-primary" />
                            Layanan
                        </h4>
                        <ul className="flex flex-col gap-2.5">
                            {SERVICE_LINKS.map(({ label, href, icon: Icon }) => (
                                <li key={label}>
                                    <Link
                                        href={href()}
                                        className="group inline-flex items-center gap-2 text-sm text-muted-foreground transition-colors duration-150 hover:text-foreground"
                                    >
                                        <Icon className="size-3.5 shrink-0 opacity-50 transition-opacity group-hover:opacity-100" />
                                        {label}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </motion.div>

                    {/* Catalog links */}
                    <motion.div
                        className="lg:col-span-2"
                        custom={2}
                        variants={fadeUp}
                        initial="hidden"
                        whileInView="show"
                        viewport={{ once: true, amount: 0.3 }}
                    >
                        <h4 className="mb-4 flex items-center gap-2 text-xs font-semibold tracking-widest text-foreground/80 uppercase">
                            <BookOpen className="size-3.5 text-primary" />
                            Katalog
                        </h4>
                        <ul className="flex flex-col gap-2.5">
                            {CATALOG_LINKS.map(
                                ({ label, href, icon: Icon }) => (
                                    <li key={label}>
                                        <Link
                                            href={href()}
                                            className="group inline-flex items-center gap-2 text-sm text-muted-foreground transition-colors duration-150 hover:text-foreground"
                                        >
                                            <Icon className="size-3.5 shrink-0 opacity-50 transition-opacity group-hover:opacity-100" />
                                            {label}
                                        </Link>
                                    </li>
                                ),
                            )}
                        </ul>
                    </motion.div>

                    {/* External / info links */}
                    <motion.div
                        className="lg:col-span-3"
                        custom={3}
                        variants={fadeUp}
                        initial="hidden"
                        whileInView="show"
                        viewport={{ once: true, amount: 0.3 }}
                    >
                        <h4 className="mb-4 flex items-center gap-2 text-xs font-semibold tracking-widest text-foreground/80 uppercase">
                            <GraduationCap className="size-3.5 text-primary" />
                            Institusi
                        </h4>
                        <ul className="flex flex-col gap-2.5">
                            {INFO_LINKS.map(({ label, href, external }) => (
                                <li key={label}>
                                    <a
                                        href={href}
                                        target={external ? '_blank' : undefined}
                                        rel={
                                            external
                                                ? 'noopener noreferrer'
                                                : undefined
                                        }
                                        className="group inline-flex items-center gap-2 text-sm text-muted-foreground transition-colors duration-150 hover:text-foreground"
                                    >
                                        <ExternalLink className="size-3.5 shrink-0 opacity-40 transition-opacity group-hover:opacity-90" />
                                        {label}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </motion.div>
                </div>

                {/* ── Bottom bar ───────────────────────────────────────── */}
                <div className="flex flex-col items-center justify-between gap-4 border-t py-5 sm:flex-row">
                    <p className="text-center text-xs text-muted-foreground sm:text-left">
                        © {year} Prodi Teknik Informatika, Universitas
                        Malikussaleh. Dibuat dengan{' '}
                        <Heart className="inline-block size-3 text-red-500/80" />{' '}
                        untuk kemajuan literasi digital.
                    </p>

                    {/* Social / external icons */}
                    <div className="flex items-center gap-1">
                        <a
                            href="https://www.unimal.ac.id"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Website Universitas Malikussaleh"
                            className="flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors duration-150 hover:bg-primary/10 hover:text-primary"
                        >
                            <Globe className="size-4" />
                        </a>
                        <a
                            href="https://github.com"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Source code"
                            className="flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors duration-150 hover:bg-primary/10 hover:text-primary"
                        >
                            <Github className="size-4" />
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    );
}
