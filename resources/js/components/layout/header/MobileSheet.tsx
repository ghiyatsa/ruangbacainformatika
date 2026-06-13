import { Link, usePage } from '@inertiajs/react';
import { ChevronDown, Menu, ShoppingCart, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    Sheet,
    SheetClose,
    SheetContent,
    SheetHeader,
    SheetTrigger,
} from '@/components/ui/sheet';
import { google } from '@/routes/auth';
import loans from '@/routes/loans';
import { AppLogo } from './AppLogo';
import { NAV_LINKS } from './constants';
import type { Auth, LoanRequestCart } from '@/types';

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
    const { loanRequestCart } = usePage<{
        loanRequestCart: LoanRequestCart | null;
    }>().props;
    const defaultOpenSections = NAV_LINKS.filter(
        (item) =>
            item.children &&
            item.children.some((child) => isActive(child.href)),
    ).map((item) => item.label);

    return (
        <Sheet open={mobileOpen} onOpenChange={setMobileOpen}>
            <SheetTrigger asChild>
                <Button
                    variant="ghost"
                    size="icon"
                    className="-ml-2 h-10 w-10 rounded-lg md:hidden"
                    aria-label={mobileOpen ? 'Tutup menu' : 'Buka menu'}
                    aria-expanded={mobileOpen}
                >
                    <Menu className="size-5" />
                </Button>
            </SheetTrigger>

            <SheetContent
                side="left"
                showCloseButton={false}
                overlayClassName="bg-black/20"
                className="h-svh w-[min(92vw,24rem)] max-w-[24rem] transform-gpu gap-0 rounded-r-[1.15rem] border-r border-border/60 bg-background p-0 shadow-none will-change-transform contain-[layout_paint] data-open:duration-200 data-closed:duration-0"
            >
                <SheetHeader className="flex h-16 flex-row items-center gap-0.5 rounded-tr-[1.15rem] border-b border-border/60 bg-background px-3 text-left">
                    <SheetClose asChild>
                        <Button
                            variant="ghost"
                            size="icon"
                            className="-ml-2 h-10 w-10 rounded-lg"
                            aria-label="Tutup menu"
                        >
                            <X className="size-5" />
                        </Button>
                    </SheetClose>
                    <AppLogo compact />
                </SheetHeader>

                <div className="flex-1 overflow-y-auto px-1 pt-2 pb-4">
                    <nav className="space-y-2">
                        {auth.user && auth.canBorrowBooks ? (
                            <SheetClose asChild>
                                <Link
                                    href={loans.request.url()}
                                    className={[
                                        'flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-medium transition-colors',
                                        isActive(loans.request.url())
                                            ? 'bg-primary/10 text-primary'
                                            : 'text-foreground hover:bg-accent/70',
                                    ].join(' ')}
                                >
                                    <ShoppingCart className="size-5 shrink-0 text-muted-foreground" />
                                    <span>Keranjang Peminjaman</span>
                                    {loanRequestCart &&
                                        loanRequestCart.count > 0 && (
                                            <span className="ml-auto inline-flex min-w-6 animate-in items-center justify-center rounded-full bg-primary px-2 py-0.5 text-[11px] font-semibold text-primary-foreground duration-200 zoom-in-50">
                                                {loanRequestCart.count}
                                            </span>
                                        )}
                                </Link>
                            </SheetClose>
                        ) : null}
                        {NAV_LINKS.map((item) => {
                            if (item.children) {
                                const isSectionActive = item.children.some(
                                    (child) => isActive(child.href),
                                );

                                return (
                                    <Collapsible
                                        key={item.label}
                                        defaultOpen={defaultOpenSections.includes(
                                            item.label,
                                        )}
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
                                                    <SheetClose
                                                        asChild
                                                        key={child.href}
                                                    >
                                                        <Link
                                                            href={child.href}
                                                            className={[
                                                                'flex items-start gap-3 rounded-xl px-3 py-2.5 transition-colors',
                                                                isActive(
                                                                    child.href,
                                                                )
                                                                    ? 'bg-primary/10 text-primary'
                                                                    : 'text-muted-foreground hover:bg-accent/70 hover:text-foreground',
                                                            ].join(' ')}
                                                        >
                                                            <ChildIcon className="mt-0.5 size-5 shrink-0" />
                                                            <div className="space-y-1">
                                                                <div className="text-sm font-medium">
                                                                    {
                                                                        child.label
                                                                    }
                                                                </div>
                                                            </div>
                                                        </Link>
                                                    </SheetClose>
                                                );
                                            })}
                                        </CollapsibleContent>
                                    </Collapsible>
                                );
                            }

                            return (
                                <SheetClose asChild key={item.label}>
                                    <Link
                                        href={item.href || '#'}
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
                                </SheetClose>
                            );
                        })}
                    </nav>
                </div>

                {!auth.user ? (
                    <div className="flex flex-col gap-2 border-t border-border/60 p-4">
                        <Button asChild className="h-11 w-full rounded-xl">
                            <SheetClose asChild>
                                <a href={google.url()}>Masuk</a>
                            </SheetClose>
                        </Button>
                    </div>
                ) : null}
            </SheetContent>
        </Sheet>
    );
}
