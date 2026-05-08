import { Link } from '@inertiajs/react';
import { Mail, MapPin, Terminal } from 'lucide-react';
import { motion } from 'motion/react';
import { home } from '@/routes';
import { fadeUp } from './constants';

export function FooterBrand() {
    return (
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
                Perpustakaan digital resmi Program Studi Teknik Informatika
                Universitas Malikussaleh. Mendukung riset, pembelajaran
                akademik, dan pengembangan literasi teknologi mahasiswa.
            </p>

            {/* Contact details */}
            <div className="flex flex-col gap-2.5">
                <div className="flex items-start gap-2.5 text-xs text-muted-foreground">
                    <MapPin className="mt-0.5 size-3.5 shrink-0 text-primary/70" />
                    <span>Jl. Cot Tengku Nie, Reuleut, Aceh Utara — 24355</span>
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
    );
}
