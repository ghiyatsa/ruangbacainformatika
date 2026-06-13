import { Link } from '@inertiajs/react';
import { BookOpen, FileCheck } from 'lucide-react';
import { motion } from 'motion/react';
import { fadeUp, KOLEKSI_LINKS, LEGAL_LINKS } from './constants';

export function FooterLinks() {
    return (
        <>
            <motion.div
                className="lg:col-span-2 lg:col-start-9"
                custom={1}
                variants={fadeUp}
                initial="hidden"
                whileInView="show"
                viewport={{ once: true, amount: 0.3 }}
            >
                <p className="mb-4 flex items-center gap-2 text-xs font-semibold tracking-widest text-foreground/80 uppercase">
                    <BookOpen className="size-3.5 text-primary" />
                    Koleksi
                </p>
                <ul className="flex flex-col gap-2.5">
                    {KOLEKSI_LINKS.map(({ label, href, icon: Icon }) => (
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

            <motion.div
                className="lg:col-span-2"
                custom={4}
                variants={fadeUp}
                initial="hidden"
                whileInView="show"
                viewport={{ once: true, amount: 0.3 }}
            >
                <p className="mb-4 flex items-center gap-2 text-xs font-semibold tracking-widest text-foreground/80 uppercase">
                    <FileCheck className="size-3.5 text-primary" />
                    Informasi
                </p>
                <ul className="flex flex-col gap-2.5">
                    {LEGAL_LINKS.map(({ label, href, icon: Icon }) => (
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
        </>
    );
}
