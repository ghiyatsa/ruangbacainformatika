import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { login, register } from '@/routes';
import type { Auth } from '@/types';
import { NAV_LINKS } from './constants';

interface MobileDrawerProps {
    mobileOpen: boolean;
    setMobileOpen: (open: boolean) => void;
    isActive: (href: string) => boolean;
    auth: Auth;
    canRegister?: boolean;
}

export function MobileDrawer({
    mobileOpen,
    setMobileOpen,
    isActive,
    auth,
    canRegister = true,
}: MobileDrawerProps) {
    return (
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
                            <Button asChild className="w-full rounded-xl text-sm">
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
    );
}
