import { Link } from '@inertiajs/react';
import { NAV_LINKS } from './constants';

interface DesktopNavProps {
    isActive: (href: string) => boolean;
}

export function DesktopNav({ isActive }: DesktopNavProps) {
    return (
        <nav className="hidden items-center gap-0.5 md:flex">
            {NAV_LINKS.map(({ label, href }) => (
                <Link
                    key={href}
                    href={href}
                    className={[
                        'relative rounded-lg px-3.5 py-2 text-sm font-medium transition-all duration-200',
                        isActive(href)
                            ? 'text-primary'
                            : 'text-muted-foreground hover:bg-accent/60 hover:text-foreground',
                    ].join(' ')}
                >
                    {label}
                </Link>
            ))}
        </nav>
    );
}
