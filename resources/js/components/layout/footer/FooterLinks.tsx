import { Link } from '@inertiajs/react';
import { KOLEKSI_LINKS, LEGAL_LINKS } from './constants';

export function FooterLinks() {
    return (
        <>
            <div className="lg:col-span-2 lg:col-start-9">
                <p className="mb-4 flex items-center gap-2 text-xs font-semibold tracking-widest text-foreground/80 uppercase">
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
            </div>

            <div className="lg:col-span-2">
                <p className="mb-4 flex items-center gap-2 text-xs font-semibold tracking-widest text-foreground/80 uppercase">
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
            </div>
        </>
    );
}
