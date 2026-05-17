import { Link } from '@inertiajs/react';
import { ChevronDown, Menu } from 'lucide-react';
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
import { register } from '@/routes';
import type { Auth } from '@/types';
import { AppLogo } from './AppLogo';
import { NAV_LINKS } from './constants';

interface MobileSheetProps {
    mobileOpen: boolean;
    setMobileOpen: (open: boolean) => void;
    isActive: (href: string) => boolean;
    auth: Auth;
    canRegister?: boolean;
}

export function MobileSheet({
    mobileOpen,
    setMobileOpen,
    isActive,
    auth,
    canRegister = true,
}: MobileSheetProps) {
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
                    className="h-9 w-9 rounded-xl md:hidden"
                    aria-label={mobileOpen ? 'Tutup menu' : 'Buka menu'}
                    aria-expanded={mobileOpen}
                >
                    <Menu className="size-5" />
                </Button>
            </SheetTrigger>

            <SheetContent
                side="right"
                className="w-[min(92vw,24rem)] border-l border-border/60 bg-background/95 p-0 backdrop-blur-xl"
            >
                <SheetHeader className="gap-3 border-b border-border/60 px-5 py-4 text-left">
                    <AppLogo />
                </SheetHeader>

                <div className="flex-1 overflow-y-auto px-3 py-4">
                    <nav className="space-y-2">
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
                                        className="rounded-2xl border border-border/50 bg-muted/20"
                                    >
                                        <CollapsibleTrigger asChild>
                                            <button
                                                type="button"
                                                className="group flex w-full items-center gap-3 rounded-2xl px-3 py-3 text-left transition-colors hover:bg-accent/70"
                                            >
                                                <item.icon className="size-4 shrink-0 text-muted-foreground" />
                                                <span
                                                    className={
                                                        isSectionActive
                                                            ? 'font-semibold text-foreground'
                                                            : 'font-medium text-foreground'
                                                    }
                                                >
                                                    {item.label}
                                                </span>
                                                <ChevronDown className="ml-auto size-4 shrink-0 text-muted-foreground transition-transform group-data-[state=open]:rotate-180" />
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
                                                            <ChildIcon className="mt-0.5 size-4 shrink-0" />
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
                                        <item.icon className="size-4 shrink-0 text-muted-foreground" />
                                        {item.label}
                                    </Link>
                                </SheetClose>
                            );
                        })}
                    </nav>
                </div>

                {!auth.user && canRegister ? (
                    <div className="border-t border-border/60 p-4">
                        <Button asChild className="h-11 w-full rounded-xl">
                            <SheetClose asChild>
                                <Link href={register.url()}>
                                    Bergabung Sekarang
                                </Link>
                            </SheetClose>
                        </Button>
                    </div>
                ) : null}
            </SheetContent>
        </Sheet>
    );
}
