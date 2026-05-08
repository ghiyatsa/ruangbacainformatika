import { Link } from '@inertiajs/react';
import { BookOpen, ExternalLink, GraduationCap, Wrench } from 'lucide-react';
import { motion } from 'motion/react';
import { CATALOG_LINKS, INFO_LINKS, SERVICE_LINKS, fadeUp } from './constants';

export function FooterLinks() {
    return (
        <>
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
                    {CATALOG_LINKS.map(({ label, href, icon: Icon }) => (
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
                                    external ? 'noopener noreferrer' : undefined
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
        </>
    );
}
