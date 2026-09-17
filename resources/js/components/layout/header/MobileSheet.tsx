import { Link } from '@inertiajs/react';
import { ChevronDown, Menu, X } from 'lucide-react';
import * as React from 'react';
import { Button } from '@/components/ui/button';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { login } from '@/routes';
import { getNavLinks } from './constants';
import type { Auth } from '@/types';

interface MobileSheetProps {
    mobileOpen: boolean;
    setMobileOpen: (open: boolean) => void;
    isActive: (href: string) => boolean;
    auth: Auth;
}

export function MobileSheet({
    mobileOpen,
    setMobileOpen,
    isActive,
    auth,
}: MobileSheetProps) {
    const isMember = Boolean(auth?.isMember);
    const navLinks = React.useMemo(() => getNavLinks(isMember), [isMember]);

    const defaultOpenSections = React.useMemo(() => {
        return navLinks
            .filter(
                (item) =>
                    item.children &&
                    item.children.some((child) => isActive(child.href)),
            )
            .map((item) => item.label);
    }, [navLinks, isActive]);

    // Close on Escape
    React.useEffect(() => {
        if (!mobileOpen) {
            return;
        }

        const handler = (e: KeyboardEvent) => {
            if (e.key === 'Escape') {
                setMobileOpen(false);
            }
        };

        document.addEventListener('keydown', handler);

        return () => document.removeEventListener('keydown', handler);
    }, [mobileOpen, setMobileOpen]);

    // Lock body scroll when open
    React.useEffect(() => {
        document.body.style.overflow = mobileOpen ? 'hidden' : '';

        return () => {
            document.body.style.overflow = '';
        };
    }, [mobileOpen]);

    return (
        <>
            {/* Hamburger / Close toggle */}
            <Button
                variant="ghost"
                size="icon"
                className="-ml-2 h-10 w-10 shrink-0 rounded-lg transition-colors active:scale-95 md:hidden"
                aria-label={mobileOpen ? 'Tutup menu' : 'Buka menu'}
                aria-expanded={mobileOpen}
                onClick={() => setMobileOpen(!mobileOpen)}
            >
                <span className="relative flex size-5 items-center justify-center">
                    <Menu
                        className={`absolute size-5 transition-all duration-200 ${mobileOpen ? 'rotate-90 opacity-0 scale-75' : 'rotate-0 opacity-100 scale-100'}`}
                    />
                    <X
                        className={`absolute size-5 transition-all duration-200 ${mobileOpen ? 'rotate-0 opacity-100 scale-100' : '-rotate-90 opacity-0 scale-75'}`}
                    />
                </span>
            </Button>

            {/* Backdrop — covers the page below the header; click to close */}
            <div
                aria-hidden="true"
                className={`fixed inset-x-0 bottom-0 z-40 h-[calc(100svh-var(--header-height,4.5rem))] bg-black/40 backdrop-blur-[2px] transition-opacity duration-300 md:hidden ${mobileOpen ? 'pointer-events-auto opacity-100' : 'pointer-events-none opacity-0'}`}
                style={{ top: 'var(--header-height, 4.5rem)' }}
                onClick={() => setMobileOpen(false)}
                data-testid="mobile-nav-backdrop"
            />

            {/* Sidebar panel — slides from left, starts below header */}
            <div
                className={`fixed left-0 z-50 h-[calc(100svh-var(--header-height,4.5rem))] w-[min(85vw,20rem)] overflow-y-auto rounded-br-[1.15rem] border-r border-border/60 bg-background px-4 shadow-lg transition-transform duration-300 ease-[cubic-bezier(0.32,0.72,0,1)] md:hidden ${mobileOpen ? 'translate-x-0' : '-translate-x-full'}`}
                style={{ top: 'var(--header-height, 4.5rem)' }}
                aria-hidden={!mobileOpen}
                // Panel yang tertutup hanya digeser keluar layar, sehingga
                // isinya masih dapat difokus keyboard. inert menonaktifkan
                // fokus dan klik sampai menu benar-benar dibuka.
                inert={!mobileOpen ? true : undefined}
            >
                <div className="pt-3 pb-4">
                    <nav className="space-y-1">
                        {navLinks.map((item) => {
                            if (item.children) {
                                const isSectionActive = item.children.some(
                                    (child) => isActive(child.href),
                                );

                                return (
                                    <Collapsible
                                        key={item.label}
                                        defaultOpen={defaultOpenSections.includes(item.label)}
                                        className="rounded-2xl border border-border/80 bg-muted/60 dark:border-border/50 dark:bg-muted/20"
                                    >
                                        <CollapsibleTrigger asChild>
                                            <button
                                                type="button"
                                                className="group flex w-full items-center gap-3 rounded-2xl px-3 py-3 text-left transition-colors hover:bg-accent/70"
                                            >
                                                <item.icon className="size-5 shrink-0 text-muted-foreground" />
                                                <span
                                                    className={
                                                        isSectionActive
                                                            ? 'font-semibold text-foreground'
                                                            : 'font-medium text-foreground'
                                                    }
                                                >
                                                    {item.label}
                                                </span>
                                                <ChevronDown className="ml-auto size-5 shrink-0 text-muted-foreground transition-transform group-data-[state=open]:rotate-180" />
                                            </button>
                                        </CollapsibleTrigger>

                                        <CollapsibleContent className="space-y-1 px-2 pb-2">
                                            {item.children.map((child) => {
                                                const ChildIcon = child.icon;

                                                return (
                                                    <Link
                                                        key={child.href}
                                                        href={child.href}
                                                        onClick={() => setMobileOpen(false)}
                                                        className={[
                                                            'flex items-start gap-3 rounded-xl px-3 py-2.5 transition-colors',
                                                            isActive(child.href)
                                                                ? 'bg-primary/10 text-primary'
                                                                : 'text-muted-foreground hover:bg-accent/70 hover:text-foreground',
                                                        ].join(' ')}
                                                    >
                                                        <ChildIcon className="mt-0.5 size-5 shrink-0" />
                                                        <div className="space-y-1">
                                                            <div className="text-sm font-medium">
                                                                {child.label}
                                                            </div>
                                                        </div>
                                                    </Link>
                                                );
                                            })}
                                        </CollapsibleContent>
                                    </Collapsible>
                                );
                            }

                            return (
                                <Link
                                    key={item.label}
                                    href={item.href || '#'}
                                    onClick={() => setMobileOpen(false)}
                                    className={[
                                        'flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-medium transition-colors',
                                        item.href && isActive(item.href)
                                            ? 'bg-primary/10 text-primary'
                                            : 'text-foreground hover:bg-accent/70',
                                    ].join(' ')}
                                >
                                    <item.icon className="size-5 shrink-0 text-muted-foreground" />
                                    {item.label}
                                </Link>
                            );
                        })}
                    </nav>

                    {!auth?.user ? (
                        <div className="mt-3 border-t border-border/60 pt-3">
                            <Button asChild className="h-11 w-full rounded-xl" onClick={() => setMobileOpen(false)}>
                                <Link href={login.url()}>Masuk</Link>
                            </Button>
                        </div>
                    ) : auth?.requiresOnboarding && auth.onboardingUrl ? (
                        <div className="mt-3 border-t border-border/60 pt-3">
                            <Button
                                asChild
                                variant="secondary"
                                className="h-11 w-full rounded-xl border border-primary/30 bg-primary/10 text-primary hover:bg-primary/20"
                                onClick={() => setMobileOpen(false)}
                            >
                                <Link href={auth.onboardingUrl}>
                                    Lengkapi Data Anggota
                                </Link>
                            </Button>
                        </div>
                    ) : null}
                </div>
            </div>
        </>
    );
}