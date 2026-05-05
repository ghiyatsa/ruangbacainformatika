import { Link, usePage } from '@inertiajs/react';
import {
    BookOpen,
    Home,
    Menu,
    MoonIcon,
    Search,
    SunIcon,
    X,
} from 'lucide-react';
import * as React from 'react';
import { GlobalSearch } from '@/components/layouts/GlobalSearch';
import { UserMenuContent } from '@/components/layouts/UserMenuContent';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { AppLogo } from '@/components/welcome/AppLogo';
import { useAppearance } from '@/hooks/use-appearance';
import { login, register } from '@/routes';
import type { Auth } from '@/types';

const NAV_LINKS = [
    { label: 'Beranda', href: '/', icon: Home },
    { label: 'Katalog', href: '/books', icon: BookOpen },
];

function UserAvatar({ name }: { name: string }) {
    const initials = name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();

    return (
        <span className="flex size-8 items-center justify-center rounded-full bg-primary text-xs font-bold text-primary-foreground shadow-sm">
            {initials}
        </span>
    );
}

export function AppHeader({ hideSearch = false }: { hideSearch?: boolean }) {
    const { auth, canRegister = true } = usePage<{
        auth: Auth;
        canRegister?: boolean;
    }>().props;
    const { appearance, updateAppearance } = useAppearance();
    const [mobileOpen, setMobileOpen] = React.useState(false);

    const currentUrl = usePage().url;

    const isActive = (href: string) => {
        if (href === '/') {
            return currentUrl === '/';
        }

        return currentUrl.startsWith(href);
    };

    const openSearch = () =>
        window.dispatchEvent(new Event('open-global-search'));

    React.useEffect(() => {
        const handleResize = () => {
            if (window.innerWidth >= 768) {
                setMobileOpen(false);
            }
        };

        window.addEventListener('resize', handleResize);

        return () => window.removeEventListener('resize', handleResize);
    }, []);

    return (
        <header className="fixed top-3 z-50 w-full px-3 sm:top-5 sm:px-5">
            {/* Main bar */}
            <div className="mx-auto flex h-14 max-w-7xl items-center justify-between gap-3 rounded-2xl border border-white/10 bg-background/60 px-3 shadow-lg shadow-black/5 backdrop-blur-xl transition-all duration-300 sm:h-16 sm:px-5 dark:bg-background/30 dark:shadow-black/20">
                {/* Left: Logo */}
                <div className="flex shrink-0 items-center gap-6">
                    <AppLogo />

                    {/* Center: Desktop nav links */}
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
                </div>

                {/* Right: Actions */}
                <div className="flex items-center gap-1">
                    {/* Center: Search trigger (desktop only, rendered as visible button) */}
                    {!hideSearch && (
                        <div className="hidden flex-1 justify-center lg:flex">
                            <GlobalSearch />
                        </div>
                    )}

                    {/* Mobile search icon */}
                    {!hideSearch && (
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-9 w-9 rounded-xl lg:hidden"
                            onClick={openSearch}
                            aria-label="Cari buku"
                        >
                            <Search className="h-[18px] w-[18px]" />
                        </Button>
                    )}

                    {/* Theme toggle */}
                    <Button
                        variant="ghost"
                        size="icon"
                        className="h-9 w-9 rounded-xl"
                        onClick={() =>
                            updateAppearance(
                                appearance === 'dark' ? 'light' : 'dark',
                            )
                        }
                        aria-label="Toggle theme"
                    >
                        {appearance === 'dark' ? (
                            <SunIcon className="h-[18px] w-[18px] text-amber-400" />
                        ) : (
                            <MoonIcon className="h-[18px] w-[18px]" />
                        )}
                        <span className="sr-only">Toggle theme</span>
                    </Button>

                    {/* Auth: logged in */}
                    {auth.user ? (
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <button
                                    className="ml-1 flex h-9 w-9 items-center justify-center rounded-full ring-2 ring-transparent transition-all duration-200 hover:ring-primary/40 focus-visible:ring-primary/60 focus-visible:outline-none"
                                    aria-label="User menu"
                                >
                                    <UserAvatar name={auth.user.name} />
                                </button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                className="w-56"
                                align="end"
                                forceMount
                            >
                                <UserMenuContent user={auth.user} />
                            </DropdownMenuContent>
                        </DropdownMenu>
                    ) : (
                        /* Auth: guest — desktop only, mobile handled in drawer */
                        <div className="hidden items-center gap-1.5 sm:flex">
                            <Button
                                variant="ghost"
                                size="sm"
                                asChild
                                className="rounded-xl text-sm"
                            >
                                <a href={login.url()}>Masuk</a>
                            </Button>
                            {canRegister && (
                                <Button
                                    asChild
                                    size="sm"
                                    className="rounded-xl text-sm shadow-md shadow-primary/15"
                                >
                                    <a href={register.url()}>Bergabung</a>
                                </Button>
                            )}
                        </div>
                    )}

                    {/* Mobile hamburger */}
                    <Button
                        variant="ghost"
                        size="icon"
                        className="relative h-9 w-9 rounded-xl md:hidden"
                        onClick={() => setMobileOpen((v) => !v)}
                        aria-label={mobileOpen ? 'Tutup menu' : 'Buka menu'}
                        aria-expanded={mobileOpen}
                    >
                        <Menu
                            className={[
                                'absolute h-5 w-5 transition-all duration-200',
                                mobileOpen
                                    ? 'scale-0 rotate-90 opacity-0'
                                    : 'scale-100 rotate-0 opacity-100',
                            ].join(' ')}
                        />
                        <X
                            className={[
                                'absolute h-5 w-5 transition-all duration-200',
                                mobileOpen
                                    ? 'scale-100 rotate-0 opacity-100'
                                    : 'scale-0 -rotate-90 opacity-0',
                            ].join(' ')}
                        />
                    </Button>
                </div>
            </div>

            {/* Mobile drawer */}
            <div
                className={[
                    'mx-auto mt-2 max-w-7xl overflow-hidden rounded-2xl border border-white/10 bg-background/80 shadow-lg shadow-black/5 backdrop-blur-xl transition-all duration-300 ease-in-out dark:bg-background/40 dark:shadow-black/20',
                    mobileOpen ? 'max-h-80 opacity-100' : 'max-h-0 opacity-0',
                ].join(' ')}
                aria-hidden={!mobileOpen}
            >
                <div className="space-y-1 p-3">
                    {/* Nav links */}
                    {NAV_LINKS.map(({ label, href, icon: Icon }) => (
                        <Link
                            key={href}
                            href={href}
                            onClick={() => setMobileOpen(false)}
                            className={[
                                'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150',
                                isActive(href)
                                    ? 'bg-primary/10 text-primary'
                                    : 'text-muted-foreground hover:bg-accent/60 hover:text-foreground',
                            ].join(' ')}
                        >
                            <Icon className="h-4 w-4 shrink-0" />
                            {label}
                            {isActive(href) && (
                                <span className="ml-auto h-1.5 w-1.5 rounded-full bg-primary" />
                            )}
                        </Link>
                    ))}

                    {/* Guest auth links */}
                    {!auth.user && (
                        <div className="mt-2 flex flex-col gap-2 border-t border-border/50 pt-3">
                            <Button
                                variant="ghost"
                                asChild
                                className="w-full justify-start rounded-xl text-sm font-medium"
                            >
                                <a
                                    href={login.url()}
                                    onClick={() => setMobileOpen(false)}
                                >
                                    Masuk
                                </a>
                            </Button>
                            {canRegister && (
                                <Button
                                    asChild
                                    className="w-full rounded-xl text-sm"
                                >
                                    <a
                                        href={register.url()}
                                        onClick={() => setMobileOpen(false)}
                                    >
                                        Bergabung Sekarang
                                    </a>
                                </Button>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </header>
    );
}
