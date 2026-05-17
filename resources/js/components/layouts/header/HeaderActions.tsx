import { Link } from '@inertiajs/react';
import { MoonIcon, Search, SunIcon } from 'lucide-react';
import { GlobalSearch } from '@/components/layouts/GlobalSearch';
import { UserMenuContent } from '@/components/layouts/UserMenuContent';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { register } from '@/routes';
import type { Auth } from '@/types';
import { UserAvatar } from './UserAvatar';

interface HeaderActionsProps {
    auth: Auth;
    canRegister?: boolean;
    resolvedAppearance: 'light' | 'dark';
    updateAppearance: (appearance: 'light' | 'dark' | 'system') => void;
    hideSearch: boolean;
}

export function HeaderActions({
    auth,
    canRegister = true,
    resolvedAppearance,
    updateAppearance,
    hideSearch,
}: HeaderActionsProps) {
    const openSearch = () =>
        window.dispatchEvent(new Event('open-global-search'));
    const isDark = resolvedAppearance === 'dark';

    return (
        <>
            {!hideSearch && (
                <div className="hidden xl:flex">
                    <GlobalSearch />
                </div>
            )}

            {!hideSearch && (
                <Button
                    variant="ghost"
                    size="icon"
                    className="h-9 w-9 rounded-xl xl:hidden"
                    onClick={openSearch}
                    aria-label="Cari buku"
                >
                    <Search className="h-[18px] w-[18px]" />
                </Button>
            )}

            <Button
                variant="ghost"
                size="icon"
                className="h-9 w-9 rounded-xl"
                onClick={() => updateAppearance(isDark ? 'light' : 'dark')}
                aria-label={isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap'}
                title={isDark ? 'Mode terang' : 'Mode gelap'}
            >
                {isDark ? (
                    <SunIcon className="h-[18px] w-[18px] text-primary" />
                ) : (
                    <MoonIcon className="h-[18px] w-[18px] text-primary" />
                )}
                <span className="sr-only">Ubah tema</span>
            </Button>

            {auth.user ? (
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <button
                            className="flex h-9 w-9 items-center justify-center rounded-full ring-2 ring-transparent transition-all duration-200 hover:ring-primary/40 focus-visible:ring-primary/60 focus-visible:outline-none"
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
            ) : canRegister ? (
                <div className="hidden items-center sm:flex">
                    <Button
                        asChild
                        size="sm"
                        className="rounded-xl text-sm shadow-md shadow-primary/15"
                    >
                        <Link href={register.url()}>Bergabung</Link>
                    </Button>
                </div>
            ) : null}
        </>
    );
}
