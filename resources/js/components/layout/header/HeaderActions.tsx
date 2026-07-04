import { Link, usePage } from '@inertiajs/react';
import { MoonIcon, Search, ShoppingCart, SunIcon } from 'lucide-react';
import { GlobalSearch } from '@/components/layout/global-search/GlobalSearch';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { login } from '@/routes';
import loans from '@/routes/loans';
import { BookmarksDropdown } from './BookmarksDropdown';
import { NotificationsDropdown } from './NotificationsDropdown';
import { UserAvatar } from './UserAvatar';
import { UserMenuContent } from './UserMenuContent';
import type { Auth, LoanRequestCart, NotificationSummary } from '@/types';

interface HeaderActionsProps {
    auth: Auth;
    resolvedAppearance: 'light' | 'dark';
    updateAppearance: (appearance: 'light' | 'dark' | 'system') => void;
    hideSearch: boolean;
}

export function HeaderActions({
    auth,
    resolvedAppearance,
    updateAppearance,
    hideSearch,
}: HeaderActionsProps) {
    const { loanRequestCart, notifications } = usePage<{
        loanRequestCart: LoanRequestCart | null;
        notifications: NotificationSummary;
    }>().props;
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

            <div className="flex items-center">
                {!hideSearch && (
                    <Button
                        variant="ghost"
                        size="icon"
                        className="h-10 w-10 rounded-xl xl:hidden"
                        onClick={openSearch}
                        aria-label="Cari buku"
                    >
                        <Search className="size-[18px] sm:size-[14px]" />
                    </Button>
                )}

                <Button
                    variant="ghost"
                    size="icon"
                    className="group hidden h-9 w-9 rounded-xl transition-all duration-300 md:inline-flex"
                    onClick={() => updateAppearance(isDark ? 'light' : 'dark')}
                    aria-label={isDark ? 'Mode terang' : 'Mode gelap'}
                    title={isDark ? 'Mode terang' : 'Mode gelap'}
                >
                    {isDark ? (
                        <SunIcon className="h-[14px] w-[14px] text-primary transition-transform duration-500 group-hover:rotate-45" />
                    ) : (
                        <MoonIcon className="h-[14px] w-[14px] text-primary transition-transform duration-500 group-hover:-rotate-12" />
                    )}
                    <span className="sr-only">Ubah tema</span>
                </Button>

                <div className="inline-flex">
                    <BookmarksDropdown />
                </div>

                {auth.user ? (
                    <>
                        {auth.canViewNotifications ? (
                            <NotificationsDropdown
                                key={notifications.unreadCount}
                                initialUnreadCount={notifications.unreadCount}
                            />
                        ) : null}

                        {auth.canBorrowBooks ? (
                            <Button
                                asChild
                                variant="ghost"
                                size="icon"
                                className="group relative hidden h-9 w-9 rounded-xl transition-all duration-300 md:inline-flex"
                            >
                                <Link
                                    href={loans.request.url()}
                                    aria-label={`Keranjang peminjaman, ${loanRequestCart?.count ?? 0} buku`}
                                    title="Keranjang peminjaman"
                                >
                                    <ShoppingCart className="h-[14px] w-[14px] text-primary transition-transform duration-300" />
                                    <span className="sr-only">
                                        Keranjang peminjaman
                                    </span>
                                    {loanRequestCart &&
                                        loanRequestCart.count > 0 && (
                                            <Badge className="absolute top-0.5 right-0.5 flex h-3 min-w-3 animate-in items-center justify-center rounded-full px-1 py-0 text-[8px] leading-none shadow-sm duration-200 zoom-in-50">
                                                {loanRequestCart.count}
                                            </Badge>
                                        )}
                                </Link>
                            </Button>
                        ) : null}
                    </>
                ) : (
                    <Button
                        asChild
                        size="sm"
                        className="hidden rounded-xl text-sm shadow-md shadow-primary/15 md:inline-flex"
                    >
                        <Link href={login.url()}>Masuk</Link>
                    </Button>
                )}

                {auth.user ? (
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <button
                                className="flex h-10 w-10 items-center justify-center rounded-full ring-2 ring-transparent transition-all duration-200 hover:ring-primary/40 focus-visible:ring-primary/60 focus-visible:outline-none sm:h-9 sm:w-9"
                                aria-label="Menu pengguna"
                            >
                                <UserAvatar
                                    name={auth.user.name}
                                    avatar={
                                        auth.user.avatar as
                                            | string
                                            | null
                                            | undefined
                                    }
                                />
                            </button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            className="w-64 p-1.5"
                            align="end"
                            forceMount
                        >
                            <UserMenuContent user={auth.user} />
                        </DropdownMenuContent>
                    </DropdownMenu>
                ) : null}
            </div>
        </>
    );
}
